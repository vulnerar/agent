<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;

it('has macro defined', function () {
    $this->assertTrue(Http::hasMacro('vulnerar'));
});

it('uses the correct configuration', function () {
    Http::fake();

    Config::set('vulnerar.host', 'example.com');
    Config::set('vulnerar.token', '<token>');

    Http::vulnerar()->get('/');

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://example.com/api/'
            && $request->hasHeader('User-Agent', 'Vulnerar Agent')
            && $request->hasHeader('Content-Type', 'application/json')
            && $request->hasHeader('Accept', 'application/json')
            && $request->hasHeader('Authorization', 'Bearer <token>');
    });
});