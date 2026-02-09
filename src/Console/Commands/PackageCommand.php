<?php

namespace Vulnerar\Agent\Console\Commands;

use Illuminate\Console\Command;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;

class PackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vulnerar:package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect package information.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $composerPath = base_path('composer.json');
        $lockPath = base_path('composer.lock');

        if (! file_exists($composerPath) || ! file_exists($lockPath)) return;

        $composer = file_get_contents($composerPath);
        $lock = file_get_contents($lockPath);

        $event = new Event(
            'package.composer',
            [
                'composer' => json_decode($composer, true),
                'lock' => json_decode($lock, true),
                'composer_encoded' => base64_encode($composer),
                'lock_encoded' => base64_encode($lock),
            ]
        );
        IngestEvents::dispatch($event);
    }
}