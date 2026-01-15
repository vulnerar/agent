<?php

namespace Vulnerar\Agent\Console\Commands;

use Illuminate\Console\Command;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;

class HeartbeatCommand extends Command
{
    protected $signature = 'vulnerar:heartbeat';

    protected $description = 'Send a heartbeat signal to the Vulnerar monitoring service';

    public function handle(): void
    {
        if (blank(config('vulnerar.host')) || blank(config('vulnerar.key'))) {
            $this->warn('Missing host or key configuration.');
            return;
        }

        IngestEvents::dispatchSync(new Event('heartbeat'));
    }
}