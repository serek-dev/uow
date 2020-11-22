<?php


namespace Stwarog\Uow;


use Stwarog\Uow\Relations\AbstractRelation;

class RelationBag
{
    private $data = [];

    private $isDirty = false;

    public function add(string $field, AbstractRelation $relation): void
    {
        $this->data[$field] = $relation;
        if ($relation->getObject()->isDirty() || $relation->getObject()->isNew()) {
            $this->isDirty = true;
        }
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
