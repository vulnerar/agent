<?php

namespace Vulnerar\Agent;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Vulnerar\Agent\Console\Commands\HeartbeatCommand;
use Vulnerar\Agent\Listeners\AuthenticationSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Vulnerar\Agent\Listeners\RequestSubscriber;

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
        Event::subscribe(RequestSubscriber::class);

        $this->publishes([
            __DIR__ . '/../config/vulnerar.php' => config_path('vulnerar.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                HeartbeatCommand::class,
            ]);
        }

        $schedule = app()->make(Schedule::class);
        $schedule->command(HeartbeatCommand::class)->everyFiveMinutes();
    }
}