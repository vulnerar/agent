<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;
use Illuminate\Support\Facades\Auth;

pest()->use(RefreshDatabase::class);

it('ingests auth.login event using Auth::attempt()', function () {
    Http::fake();

    $credentials = [
        'email' => 'jon@example.com',
        'password' => 'password',
    ];

    $user = User::factory()->create($credentials);

    Auth::attempt($credentials);

    Http::assertSent(function (Request $request) use ($user, $credentials): bool {
        return $request['type'] === 'auth.login'
            && $request['data']['login'] === $credentials['email']
            && $request['data']['password']['sha1'] === sha1($credentials['password'])
            && $request['user']['id'] === $user->id
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests auth.login event using Auth::attemptWhen()', function () {
    Http::fake();

    $credentials = [
        'email' => 'jon@example.com',
        'password' => 'password',
    ];

    $user = User::factory()->create($credentials);

    Auth::attemptWhen($credentials, function (User $user) {
        return true;
    });

    Http::assertSent(function (Request $request) use ($user, $credentials): bool {
        return $request['type'] === 'auth.login'
            && $request['data']['login'] === $credentials['email']
            && $request['data']['password']['sha1'] === sha1($credentials['password'])
            && $request['user']['id'] === $user->id
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests auth.login event using Auth::basic()', function () {
    Http::fake();

    $credentials = [
        'email' => 'jon@example.com',
        'password' => 'password',
    ];

    $user = User::factory()->create($credentials);

    $this->withBasicAuth($credentials['email'], $credentials['password'])
        ->get('/basic')
        ->assertSuccessful()
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);

    Http::assertSent(function (Request $request) use ($user, $credentials): bool {
        return $request['type'] === 'auth.login'
            && $request['data']['login'] === $credentials['email']
            && $request['data']['password']['sha1'] === sha1($credentials['password'])
            && $request['user']['id'] === $user->id
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('does not ingest auth.login events using Auth::login()', function () {
    Http::fake();

    $user = User::factory()->create();

    Auth::login($user);

    Http::assertNothingSent();
});

it('does not ingest auth.login events using Auth::loginUsingId()', function () {
    Http::fake();

    $user = User::factory()->create();

    Auth::loginUsingId($user->getKey());

    Http::assertNothingSent();
});

it('ingests auth.failed event (existing user) using Auth::attempt()', function () {
    Http::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    Auth::attempt([
        'email' => 'jon@example.com',
        'password' => 'wrong-password',
    ]);

    Http::assertSent(function (Request $request) use ($user): bool {
        return $request['type'] === 'auth.failed'
            && $request['data']['credentials']['login'] === 'jon@example.com'
            && $request['data']['credentials']['password'] === 'wron**pa**w***'
            && $request['user']['id'] === $user->id
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests auth.failed event (non-existing user) using Auth::attempt()', function () {
    Http::fake();

    User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    Auth::attempt([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    Http::assertSent(function (Request $request): bool {
        return $request['type'] === 'auth.failed'
            && $request['data']['credentials']['login'] === 'admin@example.com'
            && $request['data']['credentials']['password'] === 'pass**rd******'
            && $request['user']['id'] === null
            && $request['user']['name'] === null
            && $request['user']['login'] === null
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests auth.failed event using Auth::attemptWhen()', function () {
    Http::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    Auth::attemptWhen([
        'email' => 'jon@example.com',
        'password' => 'wrong-password',
    ], function (User $user) {
        return true;
    });

    Http::assertSent(function (Request $request) use ($user): bool {
        return $request['type'] === 'auth.failed'
            && $request['data']['credentials']['login'] === 'jon@example.com'
            && $request['data']['credentials']['password'] === 'wron**pa**w***'
            && $request['user']['id'] === $user->id
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests auth.failed event using Auth::basic()', function () {
    Http::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    $this->withBasicAuth($user->email, 'wrong-password')
        ->get('/basic')
        ->assertStatus(401);

    Http::assertSent(function (Request $request) use ($user): bool {
        return $request['type'] === 'auth.failed'
            && $request['data']['credentials']['login'] === $user->email
            && $request['data']['credentials']['password'] === 'wron**pa**w***'
            && $request['user']['id'] === $user->id
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});

it('ingests auth.failed event using Auth::once()', function () {
    Http::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    Auth::once([
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    Http::assertSent(function (Request $request) use ($user): bool {
        return $request['type'] === 'auth.failed'
            && $request['data']['credentials']['login'] === $user->email
            && $request['data']['credentials']['password'] === 'wron**pa**w***'
            && $request['user']['id'] === $user->id
            && $request['user']['name'] === $user->name
            && $request['user']['login'] === $user->email
            && $request['ip_address'] === '127.0.0.1';
    });
});