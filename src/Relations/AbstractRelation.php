<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;

abstract class AbstractRelation implements RelationInterface
{
    /** @var string */
    private $keyFrom;
    /** @var string */
    private $tableTo;
    /** @var string */
    private $keyTo;
    /** @var EntityInterface */
    private $entity;
    /** @var string */
    private $field;

    public function __construct(string $field, ?EntityInterface $entity = null, string $keyFrom, string $tableTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->tableTo = $tableTo;
        $this->keyTo   = $keyTo;
        $this->entity  = $entity;
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

    public function toArray(): array
    {
        return [$this->entity];
    }

    public function field(): string
    {
        return $this->field;
    }

    public function isDirty(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->entity->isDirty();
    }

    public function isEmpty(): bool
    {
        return empty($this->entity);
    }

    public function isNew(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->entity->isNew();
    }
}
