<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;

class ManyToMany implements RelationInterface
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

    public function __construct(string $keyFrom, string $keyThroughFrom, string $tableThrough, string $keyThroughTo, string $modelTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->keyThroughFrom = $keyThroughFrom;
        $this->tableThrough = $tableThrough;
        $this->keyThroughTo = $keyThroughTo;
        $this->modelTo = $modelTo;
        $this->keyTo = $keyTo;
    }

    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $entity): void
    {
        // TODO: Implement handleRelations() method.
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }

    public function isEmpty(): bool
    {
        // TODO: Implement isEmpty() method.
    }

    public function isDirty(): bool
    {
        // TODO: Implement isDirty() method.
    }

    public function isNew(): bool
    {
        // TODO: Implement isNew() method.
    }
}
