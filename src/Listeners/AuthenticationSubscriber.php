<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
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
                'login' => $credentials['login'] ?? null,
                'password' => [
                    'sha1' => filled($credentials['password'] ?? null)
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
                'credentials' => $this->guessCredentialFields($event->credentials, true),
                'user' => Vulnerar::resolveUserDetails($event->user),
                'ip_address' => request()?->ip(),
            ]
        );
        IngestEvents::dispatch($event);
    }

    /**
     * Guess the login and password fields from the given credentials.
     */
    private function guessCredentialFields(#[SensitiveParameter] array $credentials, bool $mask = false): array
    {
        $credentials = collect($credentials);

        return [
            'login' => $credentials->get('email')
                ?? $credentials->get('username')
                ?? $credentials->get('login'),
            'password' => $mask
                ? $this->maskPassword($credentials->get('password'))
                : $credentials->get('password'),
        ];
    }

    /**
     * Mask the given password and pad it to 14 characters.
     *
     * @example  password1234 => pass**rd**3**
     * @example  qwerty123 => qwer**12******
     */
    public function maskPassword(#[SensitiveParameter] ?string $password): ?string
    {
        if (blank($password)) return null;

        $password = substr($password, 0, 14);
        $mask = '';
        $i = 0;

        collect([4,2,1])->each(function (int $leak) use ($password, &$mask, &$i) {
            $mask .= substr($password, $i, $leak) . '**';
            $i += $leak +2;
        });
        return (string) Str::of($mask)->padRight(14, '*');
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