<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;

abstract class AbstractRelation implements RelationInterface
{
    /** @var string */
    protected $keyFrom;
    /** @var string */
    protected $tableTo;
    /** @var string */
    protected $keyTo;
    /** @var EntityInterface */
    protected $relatedEntity;

    public function __construct(string $keyFrom, string $tableTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->tableTo = $tableTo;
        $this->keyTo   = $keyTo;
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
        return [$this->relatedEntity];
    }

    public function isDirty(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->relatedEntity->isDirty();
    }

    public function isEmpty(): bool
    {
        return empty($this->relatedEntity);
    }

    public function isNew(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->relatedEntity->isNew();
    }

    public function setRelatedData(array $relatedEntities = []): void
    {
        if (empty($relatedEntities)) {
            return;
        }
        $this->relatedEntity = reset($relatedEntities);
    }
}
