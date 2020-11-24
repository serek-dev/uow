<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;

abstract class AbstractRelation implements InteractWithEntityManager
{
    /** @var string */
    private $keyFrom;
    /** @var string */
    private $tableTo;
    /** @var string */
    private $keyTo;
    /** @var EntityInterface */
    private $object;
    /** @var string */
    private $field;

    public function __construct(string $field, ?EntityInterface $object = null, string $keyFrom, string $tableTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->tableTo = $tableTo;
        $this->keyTo   = $keyTo;
        $this->object  = $object;
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
        return [$this->keyFrom, $this->tableTo, $this->keyTo];
    }

    public function getObject(): ?EntityInterface
    {
        return $this->object ?? null;
    }

    public function field(): string
    {
        return $this->field;
    }
}
