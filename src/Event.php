<?php

namespace Vulnerar\Agent;

class Event
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