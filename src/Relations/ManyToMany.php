<?php


namespace Stwarog\Uow\Relations;


class ManyToMany
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
    private $tableTo;
    /** @var string */
    private $keyTo;

    public function __construct(string $keyFrom, string $keyThroughFrom, string $tableThrough, string $keyThroughTo, string $tableTo, string $keyTo)
    {
        $this->keyFrom        = $keyFrom;
        $this->keyThroughFrom = $keyThroughFrom;
        $this->tableThrough   = $tableThrough;
        $this->keyThroughTo   = $keyThroughTo;
        $this->tableTo        = $tableTo;
        $this->keyTo          = $keyTo;
    }

    public function keyFrom(): string
    {
        return $this->keyFrom;
    }

    public function keyThroughFrom(): string
    {
        return $this->keyThroughFrom;
    }

    public function tableThrough(): string
    {
        return $this->tableThrough;
    }

    public function keyThroughTo(): string
    {
        return $this->keyThroughTo;
    }

    public function tableTo(): string
    {
        return $this->tableTo;
    }

    public function keyTo(): string
    {
        return $this->keyTo;
    }
}
