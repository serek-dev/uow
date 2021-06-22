<?php


namespace Stwarog\Uow\Shared;


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
