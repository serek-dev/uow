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
     * @param array $columns
     * @param array $values - array of arrays, or array
     */
    public function insert(string $tableName, array $columns, array $values): void;

    /**
     * @param string $tableName
     * @param array $where - array of [$field, $operator, $value]
     * @param array $columns
     * @param array $values
     */
    public function update(string $tableName, array $where, array $columns, array $values): void;

    public function delete(string $tableName, array $where): void;

    /**
     * @param string $sql
     *
     * @return  object   Database_Result for SELECT queries
     * @return  mixed    the insert id for INSERT queries
     * @return  integer  number of affected rows for all other queries
     */
    public function query(string $sql);

    /**
     * Parses all stored changes in Bag and transform them to DB queries.
     *
     * @param UnitOfWork $bag
     *
     * @return mixed
     */
    public function handleChanges(UnitOfWork $bag): void;

    public function nextAutoIncrementNo(string $table, string $idKey = 'id'): string;
}
