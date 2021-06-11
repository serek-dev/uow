<?php

declare(strict_types=1);

namespace Stwarog\Uow;

use Stwarog\Uow\UnitOfWork\UnitOfWork;

interface DBConnectionInterface extends DebugAble
{
    public function startTransaction(): void;

    public function rollbackTransaction(): void;

    public function commitTransaction(): void;

    /**
     * @param string $tableName
     * @param array<string> $columns
     * @param array<int, mixed> $values
     */
    public function insert(string $tableName, array $columns, array $values): void;

    /**
     * @param string $tableName
     * @param array{0: string, 1: string, 2: mixed}|array<array{0: string, 1: string, 2: mixed}> $where
     * - array of [$field, $operator, $value]
     * @param array<string> $columns
     * @param array<int, mixed> $values
     */
    public function update(string $tableName, array $where, array $columns, array $values): void;

    /**
     * @param string $tableName
     * @param array{0: string, 1: string, 2: mixed}|array<array{0: string, 1: string, 2: mixed}> $where
     * - array of [$field, $operator, $value]
     */
    public function delete(string $tableName, array $where): void;

    public function query(string $sql): void;

    /**
     * Parses all stored changes in Bag and transform them to DB queries.
     *
     * @param UnitOfWork $bag
     */
    public function handleChanges(UnitOfWork $bag): void;

    public function nextAutoIncrementNo(string $table, string $idKey = 'id'): string;
}
