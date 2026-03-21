<?php

namespace Vulnerar\Agent;

use Countable;
use function array_shift;
use function count;

final class RecordsBuffer implements Countable
{
    private array $records = [];

    public bool $full = false;

    public function __construct(private int $length)
    {
        //
    }

    public function write(array $record): void
    {
        if ($this->full) {
            array_shift($this->records);
        }

        $this->records[] = $record;

        $this->full = $this->count() >= $this->length;
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function pull(): array
    {
        $records = $this->records;

        $this->flush();

        return $records;
    }

    public function flush(): void
    {
        $this->records = [];
        $this->full = false;
    }
}
