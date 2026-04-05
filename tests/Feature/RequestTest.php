<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('ingests http.request events', function () {
    Http::fake();

    $response = $this->get(route('request.show', ['id' => 1]), [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer secret-token',
    ])->assertSuccessful();

    Http::assertSent(function (Request $request) use ($response): bool {
        return $request['type'] === 'http.request'
            && $request['data']['request']['method'] === 'GET'
            && $request['data']['request']['url'] === route('request.show', ['id' => 1])
            && $request['data']['request']['size'] === 0
            && $request['data']['request']['headers']['accept'] == ['application/json']
            && $request['data']['request']['headers']['authorization'] == ['Bearer [12 bytes redacted]']
            && $request['data']['route']['name'] === 'request.show'
            && $request['data']['route']['path'] === '/request/{id}'
            && $request['data']['response']['status'] === 200
            && $request['data']['response']['size'] === strlen($response->getContent())
            && $request['ip_address'] === '127.0.0.1';
    });
});