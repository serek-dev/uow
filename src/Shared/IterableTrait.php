<?php

namespace Stwarog\Uow\Shared;

/**
 * Trait IterableTrait
 * Allows to type hint return types to expected var.
 * Class that uses this must contain array<int, mixed> $data field.
 */
trait IterableTrait
{
    /** @var int $position */
    private $position = 0;

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): int
    {
        return $this->position++;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }
}
