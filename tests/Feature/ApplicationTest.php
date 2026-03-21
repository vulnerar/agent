<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Vulnerar\Agent\Console\Commands\ApplicationCommand;


it('ingests app.info event', function () {
    Http::fake();

    Artisan::call(ApplicationCommand::class);

    Http::assertSent(function (Request $request): bool {
        return $request['type'] === 'app.info'
            && $request['data']['app_url'] === config('app.url')
            && $request['data']['environment'] === app()->environment()
            && $request['data']['laravel_version'] === app()->version()
            && $request['data']['php_version'] === phpversion()
            && $request['data']['os']['name'] !== null
            && isset($request['data']['os']['user']['uid'])
            && isset($request['data']['os']['user']['gid'])
            && isset($request['data']['os']['user']['user'])
            && isset($request['data']['os']['user']['group']);
    });
});