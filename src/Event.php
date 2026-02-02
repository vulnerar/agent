<?php

namespace Vulnerar\Agent;

class Event
{
    public float $timestamp;

    public function __construct(
        public string $type,
        public array $data = [],
    ) {
        $this->timestamp = microtime(true);
    }

    public function toArray(): array
    {
        $user = $this->data['user'] ?? null;
        $ipAddress = $this->data['ip_address'] ?? null;

        unset($this->data['user'], $this->data['ip_address']);

        return [
            'type' => $this->type,
            'data' => $this->data,
            'user' => $user,
            'ip_address' => $ipAddress,
            'timestamp' => $this->timestamp,
        ];
    }
}