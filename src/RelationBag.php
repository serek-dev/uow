<?php


namespace Stwarog\Uow;


use InvalidArgumentException;
use Stwarog\Uow\Relations\AbstractRelation;

class RelationBag
{
    private $data = [];

    private $isDirty = false;

    public function add(string $field, AbstractRelation $relation): void
    {
        $this->data[$field] = $relation;

        if (empty($relation->getObject())) {
            return;
        }

        if ($relation->getObject()->isDirty() || $relation->getObject()->isNew()) {
            $this->isDirty = true;
        }
    }

    public function get(string $field): AbstractRelation
    {
        if (isset($this->data[$field]) === false) {
            throw new InvalidArgumentException(sprintf('Unable to find field %s in relation bag.', $field));
        }

        return $this->data[$field];
    }

    /**
     * @return array|AbstractRelation[]
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
