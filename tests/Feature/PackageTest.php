<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Vulnerar\Agent\Console\Commands\PackageCommand;

it('ingests package.composer event', function () {
    Http::fake();

    $composerPath = base_path('composer.json');
    $lockPath = base_path('composer.lock');

    // copy composer files to workbench
    copy(__DIR__.'/../../composer.json', $composerPath);
    copy(__DIR__.'/../../composer.lock', $lockPath);

    $composer = file_get_contents($composerPath);
    $lock = file_get_contents($lockPath);

    $exitCode = Artisan::call(PackageCommand::class);

    expect($exitCode)->toBe(0);

    Http::assertSent(function (Request $request) use ($composer, $lock): bool {
        return $request['type'] === 'package.composer'
            && $request['data']['composer'] === json_decode($composer, true)
            && $request['data']['lock'] === json_decode($lock, true)
            && $request['data']['composer_encoded'] === base64_encode($composer)
            && $request['data']['lock_encoded'] === base64_encode($lock)
            && $request['user'] === null
            && $request['ip_address'] === null;
    });
})->after(function () {
    // clean up composer files
    @unlink(base_path('composer.json'));
    @unlink(base_path('composer.lock'));
});

it('skips ingestion when composer files are missing', function () {
    Http::fake();

    $exitCode = Artisan::call(PackageCommand::class);

    expect($exitCode)->toBe(0);

    Http::assertNothingSent();
});
