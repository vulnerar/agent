<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
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
                    'size' => strlen($event->request->getContent()),
                    'files' => $this->parseUploadedFiles($event->request),
                ],
                'response' => [
                    'status' => $event->response->getStatusCode(),
                    'size' => $this->parseResponseSize($event->response),
                ],
            ]
        );
        $event->ingest();
    }

    private function parseUploadedFiles(Request $request): array
    {
        return array_values(array_map(function (UploadedFile $file): array {
            return [
                'client_name' => $file->getClientOriginalName(),
                'client_extension' => $file->getClientOriginalExtension(),
                'client_mime' => $file->getClientMimeType(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'hash' => rescue(fn () => hash('sha1', $file->getContent()), report: false),
            ];
        }, $request->files->all()));
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