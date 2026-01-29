<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Context;
use SensitiveParameter;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;
use Vulnerar\Agent\Vulnerar;

class AuthenticationSubscriber
{
    public function handleAttempting(Attempting $event): void
    {
        // Store the credentials temporarily to be used in subsequent events
        Context::addHidden('vulnerar_auth_credentials', $event->credentials);
    }

    public function handleLogin(Login $event): void
    {
        $credentials = $this->guessCredentialFields(
            Context::pullHidden('vulnerar_auth_credentials', [])
        );
        $event = new Event(
            'auth.login',
            [
                'guard' => $event->guard,
                'login' => $credentials['login'] ?? null,
                'password' => [
                    'sha1' => isset($credentials['password'])
                        ? sha1((string) $credentials['password'])
                        : null,
                ],
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
                'credentials' => $this->guessCredentialFields($event->credentials),
                'user' => Vulnerar::resolveUserDetails($event->user),
                'ip_address' => request()?->ip(),
            ]
        );
        IngestEvents::dispatch($event);
    }

    /**
     * Guess the login and password fields from the given credentials.
     */
    private function guessCredentialFields(#[SensitiveParameter] array $credentials): array
    {
        $credentials = collect($credentials);

        return [
            'login' => $credentials->get('email')
                ?? $credentials->get('username')
                ?? $credentials->get('login'),
            'password' => $credentials->get('password'),
        ];
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Attempting::class => 'handleAttempting',
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
        ];
    }
}