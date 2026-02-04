<?php

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Vulnerar\Agent\Event;
use Vulnerar\Agent\Jobs\IngestEvents;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Config;

it('can push jobs to the correct queue', function () {

    Queue::fake();

    IngestEvents::dispatch([]);

    Queue::assertPushedOn(config('vulnerar.queue'), IngestEvents::class);

    Config::set('vulnerar.queue', 'low');

    IngestEvents::dispatch([]);

    Queue::assertPushedOn('low', IngestEvents::class);
});

it('can ingest a single event', function () {

    Http::fake([
        '*' => Http::response(status: 204),
    ]);

    $event = new Event(
        type: 'test',
        data: []
    );
    $job = new IngestEvents($event);
    $job->handle();

    Http::assertSent(function (Request $request) {
        $host = config('vulnerar.host');

        return $request->method() === 'POST'
            && $request->url() === "https://{$host}/api/agent/ingest"
            && count($request['events']) === 1;
    });
});

it('can ingest multiple events', function () {

    Http::fake([
        '*' => Http::response(status: 204),
    ]);

    $event1 = new Event(
        type: 'test',
        data: []
    );
    $event2 = new Event(
        type: 'test',
        data: []
    );
    $job = new IngestEvents([$event1, $event2]);
    $job->handle();

    Http::assertSent(function (Request $request) {
        $host = config('vulnerar.host');

        return $request->method() === 'POST'
            && $request->url() === "https://{$host}/api/agent/ingest"
            && count($request['events']) === 2;
    });
});

it('skips ingestion when no events are given', function () {

    Http::fake();

    $job = new IngestEvents([]);
    $job->handle();

    Http::assertNothingSent();
});

it('throws an exception on failed request', function () {

    Http::fake([
        '*' => Http::response(status: 401),
    ]);

    $event = new Event(
        type: 'test',
        data: []
    );
    $job = new IngestEvents($event);
    $job->handle();
})->throws(RequestException::class);