<?php


namespace Stwarog\Uow;


class VirtualEntity implements EntityInterface
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

    public function generateIdValue(DBConnectionInterface $db): void
    {
        return;
    }

    public function relations(): RelationBag
    {
        return new RelationBag();
    }

    public function setId(string $id): void
    {
        return;
    }

    public function get(string $field)
    {
        return null;
    }

    public function set(string $field, $value)
    {
        return;
    }

    public function idValueGenerationStrategy(): IdGenerationStrategyInterface
    {
        return new NoIncrementIdStrategy();
    }

    public function toArray(): array
    {
        return array_combine($this->columns, $this->values);
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    public function isDirty(): bool
    {
        return false;
    }

    public function isNew(): bool
    {
        return true;
    }
}
