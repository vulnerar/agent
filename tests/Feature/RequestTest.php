<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Workbench\App\Models\User;
use Illuminate\Support\Facades\Auth;

pest()->use(RefreshDatabase::class);

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
            && $request['user'] === null
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests http.request events (authenticated)', function () {
    Http::fake();

    $user = User::factory()->create();

    Auth::login($user);

    $response = $this->get(route('request.show', ['id' => 1]), [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer secret-token',
    ])->assertSuccessful();

    Http::assertSent(function (Request $request) use ($response, $user): bool {
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
            && $request['user']['id'] === (string) $user->id
            && $request['user']['type'] === get_class($user)
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});