<?php

declare(strict_types=1);

namespace Stwarog\Uow\UnitOfWork;

use Closure;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerators\IdGenerationStrategyInterface;
use Stwarog\Uow\IdGenerators\NoIncrementIdStrategy;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Relations\RelationInterface;

# todo: this class contains some bad designs & needs test
final class VirtualEntity implements EntityInterface
{
    /** @var array<int, int|string> */
    private array $columns;
    /** @var array<int, mixed> */
    private array $values;
    private string $table;
    private string $objectHash;
    /** @var array<Closure> */
    private array $closures = [];
    private bool $isNew = true;

    /**
     * VirtualEntity constructor.
     * @param string $table
     * @param array<string> $columns
     * @param array<int, string> $values
     */
    public function __construct(string $table, array $columns, array $values)
    {
        $this->columns = $columns;
        $this->values = $values;
        $this->table = $table;
        $this->objectHash = spl_object_hash($this);
    }

    /** @inheritdoc */
    public function columns(): array
    {
        return $this->columns;
    }

    /** @inheritdoc */
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
    }

    public function relations(): RelationBag
    {
        return new RelationBag();
    }

    public function setId(string $id): void
    {
    }

    public function get(string $field): mixed
    {
        $results = $this->toArray();

        return $results[$field] ?? null;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function toArray(): array
    {
        return (array)array_combine($this->columns, $this->values);
    }

    public function set(string $field, mixed $value): void
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
        return $this->isNew;
    }

    public function objectHash(): string
    {
        return $this->objectHash;
    }

    public function addPostPersist(Closure $closure): void
    {
        $this->closures[] = $closure;
    }

    /**
     * @return array<Closure>
     */
    public function getPostPersistClosures(): array
    {
        return $this->closures;
    }

    public function noLongerNew(): void
    {
        $this->isNew = false;
    }
}
