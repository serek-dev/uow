<?php

declare(strict_types=1);

namespace Stwarog\Uow\Relations;

use Iterator;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Shared\IterableTrait;
use Stwarog\Uow\UnitOfWork\VirtualEntity;

final class ManyToMany implements RelationInterface, Iterator
{
    use IterableTrait;

    private string $keyFrom;
    private string $keyThroughFrom;
    private string $tableThrough;
    private string $keyThroughTo;
    private string $keyTo;
    /** @var EntityInterface[] */
    private array $data = [];

    public function __construct(
        string $keyFrom,
        string $keyThroughFrom,
        string $tableThrough,
        string $keyThroughTo,
        string $keyTo
    ) {
        $this->keyFrom = $keyFrom;
        $this->keyThroughFrom = $keyThroughFrom;
        $this->tableThrough = $tableThrough;
        $this->keyThroughTo = $keyThroughTo;
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
        return $this->data;
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
        $this->data = $relatedEntities;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function current(): EntityInterface
    {
        return $this->data[$this->key()];
    }
}
