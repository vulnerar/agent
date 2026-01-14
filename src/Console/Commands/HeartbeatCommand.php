<?php

namespace Vulnerar\Agent\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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

        Http::vulnerar()->post("/agent/ingest", [
            'events' => [
                [
                    'type' => 'heartbeat',
                    'timestamp' => now(),
                ]
            ]
        ]);
    }
}