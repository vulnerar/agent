<?php

namespace Vulnerar\Agent\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class AuthenticationSubscriber
{
    private LoggerInterface $log;

    public function __construct()
    {
        $this->log = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/authentication.log'),
        ]);
    }

    public function handleRegistered(Registered $event): void
    {
        $this->log->info('Registered event', [
            'login' => request()?->get('email', request()?->get('username', request()?->get('login'))),
            ...$this->getBasicInfo($event),
        ]);
    }

    public function handleLogin(Login $event): void
    {
        $this->log->info('Login event', [
            'guard' => $event->guard,
            'login' => request()?->get('email', request()?->get('username', request()?->get('login'))),
            ...$this->getBasicInfo($event),
        ]);
    }

    private function getBasicInfo(Registered|Login $event): array
    {
        return [
            'user_id' => $event->user->getAuthIdentifier(),
            'user_class' => get_class($event->user),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'environment' => app()->runningInConsole() ? 'console' : 'web',
            'timestamp' => (string) now(),
        ];
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Registered::class => 'handleRegistered',
            Login::class => 'handleLogin',
        ];
    }
}