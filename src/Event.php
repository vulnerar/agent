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
        return [
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => $this->timestamp->toIso8601String(),
        ];
    }
}