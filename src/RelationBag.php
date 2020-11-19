<?php


namespace Stwarog\Uow;


use Generator;

class RelationBag
{
    private $data = [];

    private $isDirty = false;

    public function add(EntityInterface $entity): void
    {
        $this->data[] = $entity;
        if ($entity->isNew() || $entity->isDirty()) {
            $this->isDirty = true;
        }
    }

    public function toArray(): array
    {
        return iterator_to_array($this->getData());
    }

    /**
     * @return Generator|EntityInterface[]
     */
    public function getData(): Generator
    {
        foreach ($this->data as $model) {
            yield $model;
        }
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
