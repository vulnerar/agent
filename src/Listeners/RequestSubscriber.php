<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
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

        $files = collect(Arr::flatten($event->request->allFiles()))
            ->filter(fn (mixed $file) => $file instanceof UploadedFile)
            ->map(fn (UploadedFile $file) => $this->parseUploadedFile($file))
            ->values();

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
                    'files' => $files->toArray(),
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

    private function parseUploadedFile(UploadedFile $file): array
    {
        return rescue(function () use ($file) {
            return [
                'original_name' => $file->getClientOriginalName(),
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'size' => (int) $file->getSize(),
                'sha1' => hash_file('sha1', $file->getRealPath()),
                'md5' => hash_file('md5', $file->getRealPath()),
            ];
        }, rescue: [], report: false);
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