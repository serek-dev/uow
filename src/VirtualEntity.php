<?php


namespace Stwarog\Uow;


class VirtualEntity implements PersistAble
{
    /** @var array */
    private $columns;
    /** @var array */
    private $values;
    /** @var string */
    private $table;

    public function __construct(string $table, array $columns, array $values)
    {
        $this->columns = $columns;
        $this->values = $values;
        $this->table = $table;
    }

    public function columns(): array
    {
        return $this->columns;
    }

    public function values(): array
    {
        return $this->values;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function idValue(): ?string
    {
        return null;
    }

    public function idKey(): ?string
    {
        return null;
    }

    public function originalClass(): object
    {
        return $this;
    }
}
