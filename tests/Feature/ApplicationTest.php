<?php

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Vulnerar\Agent\Console\Commands\ApplicationCommand;
use Vulnerar\Agent\Jobs\IngestEvents;

it('ingests app.info event', function () {
    Queue::fake();

    Artisan::call(ApplicationCommand::class);

    Queue::assertPushed(function (IngestEvents $job): bool {
        $event = $job->events;

        return $event->type === 'app.info'
            && $event->data['app_url'] === config('app.url')
            && $event->data['laravel_version'] === app()->version()
            && $event->data['php_version'] === phpversion();
    });
});