<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Vulnerar\Agent\Event;

final class RequestSubscriber
{
    public function handleRequestHandled(RequestHandled $event): void
    {
        $routePath = match ($routeUri = $event->request->route()?->uri()) {
            null => null,
            '/' => '/',
            default => '/' . $routeUri,
        };

        $event = new Event(
            'http.request',
            [
                'ip_address' => $event->request->ip(),
                'user' => null,
                'route' => [
                    'name' => $event->request->route()?->getName(),
                    'path' => $routePath,
                ],
                'request' => [
                    'method' => $event->request->method(),
                    'url' => $event->request->fullUrl(),
                ],
                'response' => [
                    'status' => $event->response->getStatusCode(),
                ]
            ]
        );
        $event->ingest();
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            RequestHandled::class => 'handleRequestHandled',
        ];
    }
}