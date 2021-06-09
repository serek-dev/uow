<?php

declare(strict_types=1);

namespace Stwarog\Uow\UnitOfWork;

use Closure;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerators\IdGenerationStrategyInterface;
use Stwarog\Uow\IdGenerators\NoIncrementIdStrategy;
use Stwarog\Uow\RelationBag;

# todo: this class contains some bad designs & needs test
class VirtualEntity implements EntityInterface
{
    /** @var array */
    private $columns;
    /** @var array */
    private $values;
    /** @var string */
    private $table;
    private $objectHash;
    /**
     * @var array
     */
    private $closures = [];

    public function __construct(string $table, array $columns, array $values)
    {
        $this->columns = $columns;
        $this->values = $values;
        $this->table = $table;
        $this->objectHash = spl_object_hash($this);
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

    public function originalClass()
    {
        return $this;
    }

    public function generateIdValue(DBConnectionInterface $db): void
    {
    }

    public function relations(): RelationBag
    {
        return new RelationBag();
    }

    public function setId(string $id): void
    {
    }

    public function get(string $field)
    {
        $results = $this->toArray();

        return $results[$field];
    }

    public function toArray(): array
    {
        return array_combine($this->columns, $this->values);
    }

    public function set(string $field, $value): void
    {
        $results = $this->toArray();
        $results[$field] = $value;
        $this->columns = array_keys($results);
        $this->values = array_values($results);
    }

    public function idValueGenerationStrategy(): IdGenerationStrategyInterface
    {
        return new NoIncrementIdStrategy();
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

    public function objectHash(): string
    {
        return $this->objectHash;
    }

    public function addPostPersist(Closure $closure): void
    {
        $this->closures[] = $closure;
    }

    public function getPostPersistClosures(): array
    {
        return $this->closures;
    }
}
