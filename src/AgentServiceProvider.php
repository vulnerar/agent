<?php

namespace Vulnerar\Agent;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Schedule;
use React\Http\Browser;
use React\Socket\Connector;
use React\Stream\WritableResourceStream;
use Vulnerar\Agent\Console\Commands\AgentCommand;
use Vulnerar\Agent\Console\Commands\ApplicationCommand;
use Vulnerar\Agent\Console\Commands\PackageCommand;
use Vulnerar\Agent\Listeners\AuthenticationSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Vulnerar\Agent\Listeners\RequestSubscriber;

final class AgentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/vulnerar.php', 'vulnerar');

        $this->app->instance(Agent::class, new Agent(
            new RecordsBuffer(config('vulnerar.agent.buffer')),
            new Browser(config('vulnerar.dev', false) ? new Connector([
                'dns' => '127.0.0.1',
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]) : null),
            defined('STDOUT') ? new WritableResourceStream(\STDOUT) : null,
        ));

        $this->app->scoped(Vulnerar::class, function (Application $app): Vulnerar {
            return new Vulnerar;
        });
    }

    public function boot(): void
    {
        Event::subscribe(AuthenticationSubscriber::class);
        Event::subscribe(RequestSubscriber::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                AgentCommand::class,
                ApplicationCommand::class,
                PackageCommand::class,
            ]);

            Schedule::command(PackageCommand::class)->daily();
        }
    }
}