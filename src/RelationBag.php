<?php


namespace Stwarog\Uow;


use InvalidArgumentException;
use Stwarog\Uow\Relations\RelationInterface;

class RelationBag
{
    private $data = [];

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
            throw new InvalidArgumentException(sprintf('Unable to find field %s in relation bag.', $field));
        }

        return $this->data[$field];
    }

    /**
     * @return array|RelationInterface[]
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
}
