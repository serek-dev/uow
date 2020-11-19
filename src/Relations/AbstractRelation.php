<?php


namespace Stwarog\Uow\Relations;


abstract class AbstractRelation
{
    /** @var string */
    private $keyFrom;
    /** @var string */
    private $tableTo;
    /** @var string */
    private $keyTo;

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

    public function toArray(): array
    {
        return [$this->keyFrom, $this->tableTo, $this->keyTo];
    }
}
