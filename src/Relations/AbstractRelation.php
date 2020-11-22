<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;

abstract class AbstractRelation
{
    /** @var string */
    private $keyFrom;
    /** @var string */
    private $tableTo;
    /** @var string */
    private $keyTo;
    /** @var EntityInterface */
    private $object;

    public function __construct(EntityInterface $object, string $keyFrom, string $tableTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->tableTo = $tableTo;
        $this->keyTo   = $keyTo;
        $this->object  = $object;
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

    public function getObject(): EntityInterface
    {
        return $this->object;
    }
}
