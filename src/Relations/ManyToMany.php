<?php

declare(strict_types=1);

namespace Stwarog\Uow\Relations;

use Iterator;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\UnitOfWork\VirtualEntity;

class ManyToMany implements RelationInterface, Iterator
{
    /** @var string */
    private $keyFrom;
    /** @var string */
    private $keyThroughFrom;
    /** @var string */
    private $tableThrough;
    /** @var string */
    private $keyThroughTo;
    /** @var string */
    private $modelTo;
    /** @var string */
    private $keyTo;
    /** @var EntityInterface[] */
    private $related = [];

    public function __construct(
        string $keyFrom,
        string $keyThroughFrom,
        string $tableThrough,
        string $keyThroughTo,
        string $modelTo,
        string $keyTo
    ) {
        $this->keyFrom = $keyFrom;
        $this->keyThroughFrom = $keyThroughFrom;
        $this->tableThrough = $tableThrough;
        $this->keyThroughTo = $keyThroughTo;
        $this->modelTo = $modelTo;
        $this->keyTo = $keyTo;
    }

    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        foreach ($this as $relatedEntity) {
            $parentEntity->addPostPersist(
                function (EntityInterface $parentEntity) use ($entityManager, $relatedEntity) {
                    $entityManager->persist($relatedEntity);
                    $virtualEntity = new VirtualEntity(
                        $this->tableThrough,
                        [$this->keyThroughFrom, $this->keyThroughTo],
                        [$parentEntity->get($this->keyFrom), $relatedEntity->get($this->keyTo)]
                    );
                    $entityManager->persist($virtualEntity);
                }
            );
        }
    }

    public function toArray(): array
    {
        return $this->related;
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

    public function isEmpty(): bool
    {
        return empty($this->related);
    }

    /**
     * @return EntityInterface|bool
     */
    public function current()
    {
        return current($this->related);
    }

    public function next(): void
    {
        next($this->related);
    }

    /**
     * @return int|string|null
     */
    public function key()
    {
        return key($this->related);
    }

    public function valid(): bool
    {
        return key($this->related) !== null;
    }

    public function rewind(): void
    {
        reset($this->related);
    }
}
