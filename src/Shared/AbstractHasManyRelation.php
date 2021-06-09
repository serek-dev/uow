<?php

declare(strict_types=1);

namespace Stwarog\Uow\Shared;

use Iterator;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Relations\HasRelationFromToSchema;
use Stwarog\Uow\Relations\RelationInterface;

class AbstractHasManyRelation implements RelationInterface, HasRelationFromToSchema, Iterator
{
    /** @var string */
    protected $keyFrom;
    /** @var string */
    protected $tableTo;
    /** @var string */
    protected $keyTo;
    /** @var EntityInterface[] */
    protected $related = [];

    public function __construct(string $keyFrom, string $tableTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->tableTo = $tableTo;
        $this->keyTo = $keyTo;
    }

    public function keyFrom(): string
    {
        return $this->keyFrom;
    }

    public function tableTo(): string
    {
        return $this->tableTo;
    }

    public function keyTo(): string
    {
        return $this->keyTo;
    }

    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        foreach ($this as $relatedEntity) {
            if (empty($relatedEntity->get($this->keyTo))) {
                $relatedEntity->set($this->keyTo, $parentEntity->get($this->keyFrom));
            }
            $entityManager->persist($relatedEntity);
        }
    }

    public function toArray(): array
    {
        return $this->related;
    }

    public function isEmpty(): bool
    {
        return empty($this->related);
    }

    public function isDirty(): bool
    {
        foreach ($this as $entity) {
            if ($entity->isDirty() || $entity->isNew()) {
                return true;
            }
        }

        return false;
    }

    public function isNew(): bool
    {
        foreach ($this as $entity) {
            if ($entity->isDirty()) {
                return false;
            }
        }

        return true;
    }

    public function setRelatedData(array $relatedEntities = []): void
    {
        $this->related = $relatedEntities;
    }

    public function current()
    {
        return current($this->related);
    }

    public function next()
    {
        next($this->related);
    }

    public function key()
    {
        return key($this->related);
    }

    public function valid()
    {
        return key($this->related) !== null;
    }

    public function rewind()
    {
        reset($this->related);
    }
}
