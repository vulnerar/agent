<?php

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Vulnerar\Agent\Console\Commands\PackageCommand;
use Vulnerar\Agent\Jobs\IngestEvents;

it('ingests package.composer event', function () {
    Queue::fake();

    $composerPath = base_path('composer.json');
    $lockPath = base_path('composer.lock');

    // copy composer files to workbench
    copy(__DIR__.'/../../composer.json', $composerPath);
    copy(__DIR__.'/../../composer.lock', $lockPath);

    $composer = file_get_contents($composerPath);
    $lock = file_get_contents($lockPath);

    $exitCode = Artisan::call(PackageCommand::class);

    expect($exitCode)->toBe(0);

    Queue::assertPushed(function (IngestEvents $job) use ($composer, $lock): bool {
        $event = $job->events;

        return $event->type === 'package.composer'
            && $event->data['composer'] === json_decode($composer, true)
            && $event->data['lock'] === json_decode($lock, true)
            && $event->data['composer_encoded'] === base64_encode($composer)
            && $event->data['lock_encoded'] === base64_encode($lock)
            && $event->user === null
            && $event->ipAddress === null;
    });
})->after(function () {
    // clean up composer files
    @unlink(base_path('composer.json'));
    @unlink(base_path('composer.lock'));
});

it('skips ingestion when composer files are missing', function () {
    Queue::fake();

    $exitCode = Artisan::call(PackageCommand::class);

    expect($exitCode)->toBe(0);

    Queue::assertNothingPushed();
});
