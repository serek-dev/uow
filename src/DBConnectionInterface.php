<?php


namespace Stwarog\Uow;


interface DBConnectionInterface extends DebugAble
{
    public function startTransaction(): void;

    public function rollbackTransaction(): void;

    public function commitTransaction(): void;

    /**
     * @param string $tableName
     * @param array  $columns
     * @param array  $values - array of arrays, or array
     */
    public function insert(string $tableName, array $columns, array $values): void;

    /**
     * @param string $tableName
     * @param array  $where - array of [$field, $operator, $value]
     * @param array  $columns
     * @param array  $values
     */
    public function update(string $tableName, array $where, array $columns, array $values): void;

    public function delete(string $tableName, array $where): void;

    public function query(string $sql): void;

    /**
     * Parses all stored changes in Bag and transform them to DB queries.
     *
     * @param ChangesBag $bag
     *
     * @return mixed
     */
    public function handleChanges(ChangesBag $bag): void;
}
