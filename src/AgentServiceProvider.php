<?php

namespace Vulnerar\Agent;

use Vulnerar\Agent\Listeners\AuthenticationSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AgentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::subscribe(AuthenticationSubscriber::class);
    }
}