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

namespace Stwarog\Uow;


use Stwarog\Uow\UnitOfWork\UnitOfWork;

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
