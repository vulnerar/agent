<?php

namespace Vulnerar\Agent\Console\Commands;

use Illuminate\Console\Command;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;

class ApplicationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vulnerar:application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect application information.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $event = new Event(
            'app.info',
            [
                'app_url' => config('app.url'),
                'laravel_version' => app()->version(),
                'php_version' => phpversion(),
            ]
        );
        IngestEvents::dispatch($event);
    }
}