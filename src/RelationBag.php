<?php


namespace Stwarog\Uow;


use Generator;

class RelationBag
{
    private $data = [];

    private $isDirty = false;

    public function add(string $type, EntityInterface $entity): void
    {
        $this->data[$type][] = $entity;
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
        foreach ($this->data as $type => $model) {
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

    public function hasRelations(RelationType $type): bool
    {
        return isset($this->data[(string)$type]);
    }

    /**
     * @param RelationType $type
     *
     * @return array|EntityInterface[]
     */
    public function get(RelationType $type): array
    {
        return $this->data[(string) $type] ?? [];
    }
}
