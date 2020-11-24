<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;

class HasMany implements RelationInterface, HasRelationFromToSchema
{
    /** @var string */
    private $keyFrom;
    /** @var string */
    private $tableTo;
    /** @var string */
    private $keyTo;
    /** @var EntityInterface[] */
    private $related;
    /** @var string */
    private $field;

    public function __construct(string $field, array $related = [], string $keyFrom, string $tableTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->tableTo = $tableTo;
        $this->keyTo   = $keyTo;
        $this->related  = array_filter($related, function(EntityInterface $entity) {
            return true;
        });
        $this->field   = $field;
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

    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $entity): void
    {
//        dd($entity);
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
}
