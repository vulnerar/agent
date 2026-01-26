<?php

namespace Vulnerar\Agent;

use Carbon\CarbonInterface;

class Event
{
    public function __construct(
        public string $type,
        public array $data = [],
        public ?CarbonInterface $timestamp = null,
    ) {
        $this->timestamp ??= now();
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
            'timestamp' => $this->timestamp->toIso8601String(),
        ];
    }
}