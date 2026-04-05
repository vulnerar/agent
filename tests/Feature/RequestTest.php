<?php

use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

it('ingests http.request events', function () {
    Http::fake();

    $response = $this->get(route('request.show', ['id' => 1]))
        ->assertSuccessful();

    Http::assertSent(function (Request $request) use ($response): bool {
        return $request['type'] === 'http.request'
            && $request['data']['request']['method'] === 'GET'
            && $request['data']['request']['url'] === route('request.show', ['id' => 1])
            && $request['data']['request']['size'] === 0
            && $request['data']['request']['files'] === []
            && $request['data']['route']['name'] === 'request.show'
            && $request['data']['route']['path'] === '/request/{id}'
            && $request['data']['response']['status'] === 200
            && $request['data']['response']['size'] === strlen($response->getContent())
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests http.request events with uploaded files', function () {
    Http::fake();

    $file = UploadedFile::fake()
        ->createWithContent('index.html', '<html><body>Hello, World!</body></html>');

    $response = $this->post(route('upload'), ['file' => $file])
        ->assertSuccessful();

    Http::assertSent(function (Request $request) use ($response, $file): bool {
        return $request['type'] === 'http.request'
            && $request['data']['request']['method'] === 'POST'
            && $request['data']['request']['url'] === route('upload')
            && $request['data']['request']['size'] === 0
            && $request['data']['request']['files'] === [
                [
                    'client_name' => $file->getClientOriginalName(),
                    'client_extension' => $file->getClientOriginalExtension(),
                    'client_mime' => $file->getClientMimeType(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'hash' => rescue(fn () => hash('sha1', $file->getContent()), report: false),
                ]
            ]
            && $request['data']['route']['name'] === 'upload'
            && $request['data']['route']['path'] === '/upload'
            && $request['data']['response']['status'] === 200
            && $request['data']['response']['size'] === strlen($response->getContent())
            && $request['ip_address'] === '127.0.0.1';
    });
});