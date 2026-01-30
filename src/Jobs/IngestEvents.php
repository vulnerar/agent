<?php

namespace Vulnerar\Agent\Jobs;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Vulnerar\Agent\Event;

class IngestEvents implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Event|Event[] $events
     */
    public function __construct(
        public array|Event $events,
    ) {
        $this->onQueue(config('vulnerar.queue'));
    }

    public function handle(): void
    {
        $events = Arr::wrap($this->events);

        Http::vulnerar()->post('/agent/ingest', [
            'events' => array_map(function (Event $event) {
                return $event->toArray();
            }, $events),
        ]);
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function retryUntil(): DateTime
    {
        return now()->plus(days: 14);
    }
}