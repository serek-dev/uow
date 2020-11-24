<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\VirtualEntity;

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
    /** @var EntityInterface[] */
    private $related = [];

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
        $virtualEntity = new VirtualEntity(
            ['keyFrom', 'keyTo', '', '', ''],
            ['']
        );
        dd($this);
        dd($this->related);
    }

    public function toArray(): array
    {
        $this->related;
    }

    public function isDirty(): bool
    {
        foreach ($this->related as $entity) {
            if ($entity->isDirty() || $entity->isNew()) {
                return true;
            }
        }

        return false;
    }

    public function isNew(): bool
    {
        foreach ($this->related as $entity) {
            if ($entity->isDirty()) {
                return false;
            }
        }

        return true;
    }

    public function setRelatedData(array $relatedEntities = []): void
    {
        if (empty($relatedEntities)) {
            return;
        }
        $this->related = array_filter(
            $relatedEntities,
            function (EntityInterface $entity) {
                return true;
            }
        );
    }

    public function isEmpty(): bool
    {
        return empty($this->related);
    }
}
