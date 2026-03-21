<?php

namespace Vulnerar\Agent;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class Event
{
    public ?array $user = null;
    public ?string $ipAddress = null;
    public float $timestamp;

    public function __construct(
        public string $type,
        public array $data = [],
    ) {
        $this->timestamp = microtime(true);

        $this->user = $this->data['user'] ?? null;
        $this->ipAddress = $this->data['ip_address'] ?? null;

        unset($this->data['user'], $this->data['ip_address']);
    }

    public function ingest(): void
    {
        $port = config('vulnerar.agent.port');
        $timeout = config('vulnerar.agent.timeout');

        try {
            Http::timeout($timeout)
                ->connectTimeout($timeout)
                ->asJson()
                ->post("http://127.0.0.1:$port/", $this->toArray());
        } catch (ConnectionException $e) {}
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'user' => $this->user,
            'ip_address' => $this->ipAddress,
            'timestamp' => $this->timestamp,
        ];
    }
}