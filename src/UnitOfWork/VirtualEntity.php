<?php declare(strict_types=1);
/*
    Copyright (c) 2020 Sebastian Twaróg <contact@stwarog.com>

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Stwarog\Uow\UnitOfWork;


use Closure;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\Exceptions\RuntimeUOWException;
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
        $this->columns    = $columns;
        $this->values     = $values;
        $this->table      = $table;
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

    public function originalClass(): object
    {
        return $this;
    }

    public function generateIdValue(DBConnectionInterface $db): void
    {
        throw new RuntimeUOWException(sprintf('Method %s should not be used in VirtualEntity.', __METHOD__));
    }

    public function relations(): RelationBag
    {
        return new RelationBag();
    }

    public function setId(string $id): void
    {
        throw new RuntimeUOWException(sprintf('Method %s should not be used in VirtualEntity.', __METHOD__));
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
        $results         = $this->toArray();
        $results[$field] = $value;
        $this->columns   = array_keys($results);
        $this->values    = array_values($results);
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
