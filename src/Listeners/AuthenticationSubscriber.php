<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Events\Dispatcher;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;
use Vulnerar\Agent\Vulnerar;

class AuthenticationSubscriber
{
    public function handleRegistered(Registered $event): void
    {
        $event = new Event(
            'auth.registered',
            [
                'login' => request()?->get('email', request()?->get('username', request()?->get('login'))),
                'user' => Vulnerar::resolveUserDetails($event->user),
                'ip_address' => request()?->ip(),
            ],

        );
        IngestEvents::dispatch($event);
    }

    public function handleLogin(Login $event): void
    {
        $event = new Event(
            'auth.login',
            [
                'guard' => $event->guard,
                'login' => request()?->get('email', request()?->get('username', request()?->get('login'))),
                'user' => Vulnerar::resolveUserDetails($event->user),
                'ip_address' => request()?->ip(),
            ]
        );
        IngestEvents::dispatch($event);
    }

    public function handleFailed(Failed $event): void
    {
        $event = new Event(
            'auth.failed',
            [
                'guard' => $event->guard,
                'credentials' => $event->credentials,
                'user' => Vulnerar::resolveUserDetails($event->user),
                'ip_address' => request()?->ip(),
            ]
        );
        IngestEvents::dispatch($event);
    }

    public function handleVerified(Verified $event): void
    {
        $event = new Event(
            'auth.verified',
            [
                'user' => Vulnerar::resolveUserDetails($event->user), // @phpstan-ignore-line
                'ip_address' => request()?->ip(),
            ]
        );
        IngestEvents::dispatch($event);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Registered::class => 'handleRegistered',
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
        ];
    }
}