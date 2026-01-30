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
        $this->app->scoped(Vulnerar::class, function (Application $app): Vulnerar {
            return new Vulnerar;
        });
    }

    public function boot(): void
    {
        Http::macro('vulnerar', function () {
            $host = config('vulnerar.host');
            $key = config('vulnerar.key');

            return Http::baseUrl("https://{$host}/api/")
                ->asJson()
                ->acceptJson()
                ->withToken($key);
        });

        Event::subscribe(AuthenticationSubscriber::class);

        $this->publishes([
            __DIR__ . '/../config/vulnerar.php' => config_path('vulnerar.php'),
        ]);
    }
}