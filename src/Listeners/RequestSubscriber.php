<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;
use Vulnerar\Agent\Vulnerar;

class RequestSubscriber
{
    /**
     * @todo redact sensitive headers like Authorization, Cookie, Proxy-Authorization, X-XSRF-TOKEN
     */
    public function handleRequestHandled(RequestHandled $event): void
    {
        $event = new Event(
            'http.request',
            [
                'ip_address' => $event->request->ip(),
                'user' => Vulnerar::resolveUserDetails($event->request->user()),
                'request' => [
                    'method' => $event->request->method(),
                    'url' => $event->request->fullUrl(),
                    'headers' => $event->request->headers->all(),
                ],
                'response' => [
                    'status' => $event->response->getStatusCode(),
                    'headers' => $event->response->headers->all(),
                ]
            ]
        );
        IngestEvents::dispatch($event);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            RequestHandled::class => 'handleRequestHandled',
        ];
    }
}