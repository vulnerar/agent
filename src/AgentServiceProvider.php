<?php

namespace Vulnerar\Agent;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Vulnerar\Agent\Listeners\AuthenticationSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AgentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/vulnerar.php', 'vulnerar');

        $this->app->scoped(Vulnerar::class, function (Application $app): Vulnerar {
            return new Vulnerar;
        });
    }

    public function boot(): void
    {
        Http::macro('vulnerar', function () {
            $host = config('vulnerar.host');
            $token = config('vulnerar.token');

            return Http::baseUrl("https://{$host}/api/")
                ->withUserAgent('Vulnerar Agent')
                ->asJson()
                ->acceptJson()
                ->withToken($token);
        });

        Event::subscribe(AuthenticationSubscriber::class);
    }
}