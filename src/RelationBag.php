<?php

declare(strict_types=1);

namespace Stwarog\Uow;

use Iterator;
use Stwarog\Uow\Exceptions\OutOfRangeUOWException;
use Stwarog\Uow\Relations\RelationInterface;

/** @template T */
class RelationBag implements Iterator
{
    /** @var array<string, RelationInterface> */
    private array $data = [];
    private bool $isDirty = false;

    public function add(string $field, RelationInterface $relation): void
    {
        $this->data[$field] = $relation;

        if ($relation->isEmpty()) {
            return;
        }

        if ($relation->isDirty() || $relation->isNew()) {
            $this->isDirty = true;
        }
    }

    public function get(string $field): RelationInterface
    {
        if (isset($this->data[$field]) === false) {
            throw new OutOfRangeUOWException(sprintf('Unable to find field %s in relation bag.', $field));
        }

        return $this->data[$field];
    }

    /**
     * @return array<RelationInterface>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    public function current(): RelationInterface|bool
    {
        return current($this->data);
    }

    public function next(): RelationInterface|bool
    {
        return next($this->data);
    }

    public function key(): int|string|null
    {
        return key($this->data);
    }

    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    public function rewind(): void
    {
        reset($this->data);
    }
}
