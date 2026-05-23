<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Vulnerar\Agent\Concerns\RedactsHeaders;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\UserProvider;

final class RequestSubscriber
{
    use RedactsHeaders;

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
                'route' => [
                    'name' => $event->request->route()?->getName(),
                    'path' => $routePath,
                ],
                'request' => [
                    'method' => $event->request->method(),
                    'url' => $event->request->fullUrl(),
                    'size' => strlen($event->request->getContent()),
                    'headers' => $this->redactHeaders($event->request->headers)->all(),
                ],
                'response' => [
                    'status' => $event->response->getStatusCode(),
                    'size' => $this->parseResponseSize($event->response),
                ],
                'user' => UserProvider::details(),
                'ip_address' => $event->request->ip(),
            ]
        );
        $event->ingest();
    }

    private function parseResponseSize(Response $response): int
    {
        if (is_string($content = $response->getContent())) {
            return strlen($content);
        }

        if ($response instanceof BinaryFileResponse) {
            try {
                if (is_int($size = $response->getFile()->getSize())) {
                    return $size;
                }
            } catch (Throwable) {}
        }

        if (is_numeric($length = $response->headers->get('content-length'))) {
            return (int) $length;
        }

        return 0;
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            RequestHandled::class => 'handleRequestHandled',
        ];
    }
}