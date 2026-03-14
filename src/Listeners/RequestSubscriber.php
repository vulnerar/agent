<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Vulnerar\Agent\Concerns\RedactsHeaders;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;
use Vulnerar\Agent\Vulnerar;

class RequestSubscriber
{
    use RedactsHeaders;

    public function handleRequestHandled(RequestHandled $event): void
    {
        $headers = clone $event->request->headers;

        $event = new Event(
            'http.request',
            [
                'ip_address' => $event->request->ip(),
                'user' => Vulnerar::resolveUserDetails($event->request->user()),
                'request' => [
                    'method' => $event->request->method(),
                    'url' => $event->request->fullUrl(),
                    'headers' => $this->redactHeaders($headers, config('vulnerar.redact_headers'))->all(),
                ],
                'response' => [
                    'status' => $event->response->getStatusCode(),
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