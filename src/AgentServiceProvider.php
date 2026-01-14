<?php

namespace Vulnerar\Agent;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use Vulnerar\Agent\Console\Commands\HeartbeatCommand;
use Vulnerar\Agent\Listeners\AuthenticationSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AgentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Http::macro('vulnerar', function () {
            $host = config('vulnerar.host');
            $key = config('vulnerar.key');

            return Http::baseUrl("https://${host}/api/")
                ->asJson()
                ->withToken($key);
        });

        Event::subscribe(AuthenticationSubscriber::class);

        $this->publishes([
            __DIR__.'/../config/vulnerar.php' => config_path('vulnerar.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                HeartbeatCommand::class,
            ]);

            $schedule = app()->make(Schedule::class);
            $schedule->command(HeartbeatCommand::class)->everyFiveMinutes();
        }
    }
}