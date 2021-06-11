<?php

declare(strict_types=1);

namespace Stwarog\Uow;

use Iterator;
use Stwarog\Uow\Exceptions\OutOfRangeUOWException;
use Stwarog\Uow\Relations\RelationInterface;

class RelationBag implements Iterator
{
    /** @var array<string, RelationInterface> */
    private $data = [];
    /** @var bool */
    private $isDirty = false;

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

    /**
     * @return RelationInterface|bool
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @return RelationInterface|bool
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * @return int|string|null
     */
    public function key()
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
