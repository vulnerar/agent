<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('ingests http.request events', function () {
    Http::fake();

    $this->get(route('request.show', ['id' => 1]))
        ->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        return $request['type'] === 'http.request'
            && $request['data']['request']['method'] === 'GET'
            && $request['data']['request']['url'] === route('request.show', ['id' => 1])
            && $request['data']['route']['name'] === 'request.show'
            && $request['data']['route']['path'] === '/request/{id}'
            && $request['data']['response']['status'] === 200
            && $request['ip_address'] === '127.0.0.1';
    });
});