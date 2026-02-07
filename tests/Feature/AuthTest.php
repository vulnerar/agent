<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vulnerar\Agent\Jobs\IngestEvents;
use Workbench\App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;

pest()->use(RefreshDatabase::class);

it('ingests auth.login event using Auth::attempt()', function () {
    Queue::fake();

    $credentials = [
        'email' => 'jon@example.com',
        'password' => 'password',
    ];

    $user = User::factory()->create($credentials);

    Auth::attempt($credentials);

    Queue::assertPushed(function (IngestEvents $job) use ($user, $credentials) {
        $event = $job->events;

        return $event->type === 'auth.login'
            && $event->data['login'] === $credentials['email']
            && $event->data['password']['sha1'] === sha1($credentials['password'])
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.login event using Auth::attemptWhen()', function () {
    Queue::fake();

    $credentials = [
        'email' => 'jon@example.com',
        'password' => 'password',
    ];

    $user = User::factory()->create($credentials);

    Auth::attemptWhen($credentials, function (User $user) {
        return true;
    });

    Queue::assertPushed(function (IngestEvents $job) use ($user, $credentials) {
        $event = $job->events;

        return $event->type === 'auth.login'
            && $event->data['login'] === $credentials['email']
            && $event->data['password']['sha1'] === sha1($credentials['password'])
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.login event using Auth::basic()', function () {
    Queue::fake();

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

    Queue::assertPushed(function (IngestEvents $job) use ($user, $credentials) {
        $event = $job->events;

        return $event->type === 'auth.login'
            && $event->data['login'] === $credentials['email']
            && $event->data['password']['sha1'] === sha1($credentials['password'])
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.login event using Auth::login()', function () {
    Queue::fake();

    $user = User::factory()->create();

    Auth::login($user);

    Queue::assertPushed(function (IngestEvents $job) use ($user) {
        $event = $job->events;

        return $event->type === 'auth.login'
            && $event->data['login'] === null
            && $event->data['password']['sha1'] === null
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.login event using Auth::loginUsingId()', function () {
    Queue::fake();

    $user = User::factory()->create();

    Auth::loginUsingId($user->getKey());

    Queue::assertPushed(function (IngestEvents $job) use ($user) {
        $event = $job->events;

        return $event->type === 'auth.login'
            && $event->data['login'] === null
            && $event->data['password']['sha1'] === null
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.failed event (existing user) using Auth::attempt()', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    Auth::attempt([
        'email' => 'jon@example.com',
        'password' => 'wrong-password',
    ]);

    Queue::assertPushed(function (IngestEvents $job) use ($user) {
        $event = $job->events;

        return $event->type === 'auth.failed'
            && $event->data['credentials']['login'] === 'jon@example.com'
            && $event->data['credentials']['password'] === 'wron**pa**w***'
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.failed event (non-existing user) using Auth::attempt()', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    Auth::attempt([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    Queue::assertPushed(function (IngestEvents $job) use ($user) {
        $event = $job->events;

        return $event->type === 'auth.failed'
            && $event->data['credentials']['login'] === 'admin@example.com'
            && $event->data['credentials']['password'] === 'pass**rd******'
            && $event->user['id'] === null
            && $event->user['name'] === null
            && $event->user['login'] === null
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.failed event using Auth::attemptWhen()', function () {
    Queue::fake();

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

    Queue::assertPushed(function (IngestEvents $job) use ($user) {
        $event = $job->events;

        return $event->type === 'auth.failed'
            && $event->data['credentials']['login'] === 'jon@example.com'
            && $event->data['credentials']['password'] === 'wron**pa**w***'
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.failed event using Auth::basic()', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    $this->withBasicAuth($user->email, 'wrong-password')
        ->get('/basic')
        ->assertStatus(401);

    Queue::assertPushed(function (IngestEvents $job) use ($user) {
        $event = $job->events;

        return $event->type === 'auth.failed'
            && $event->data['credentials']['login'] === $user->email
            && $event->data['credentials']['password'] === 'wron**pa**w***'
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});

it('ingests auth.failed event using Auth::once()', function () {
    Queue::fake();

    $user = User::factory()->create([
        'email' => 'jon@example.com',
        'password' => 'password',
    ]);

    Auth::once([
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    Queue::assertPushed(function (IngestEvents $job) use ($user) {
        $event = $job->events;

        return $event->type === 'auth.failed'
            && $event->data['credentials']['login'] === $user->email
            && $event->data['credentials']['password'] === 'wron**pa**w***'
            && $event->user['id'] === $user->id
            && $event->user['name'] === $user->name
            && $event->user['login'] === $user->email
            && $event->ipAddress === '127.0.0.1';
    });
});