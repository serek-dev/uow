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

namespace Stwarog\Uow\Shared;


use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\UnitOfWork\ActionType;
use Stwarog\Uow\UnitOfWork\UnitOfWork;
use const PHP_EOL;

abstract class AbstractDBAdapter implements DBConnectionInterface
{
    protected $sql = '';
    protected $startTimestamp;
    protected $stopTimestamp;

    public function debug(): array
    {
        $debug['sql']  = $this->sql;
        $debug['time'] = round($this->stopTimestamp - $this->startTimestamp, 4);

        foreach (ActionType::keys() as $type) {
            $debug[$type] = substr_count($this->sql, $type);
        }

        return $debug;
    }

    public function startTransaction(): void
    {
        $this->sql = '';
        $this->log('START TRANSACTION');
        $this->startTimestamp = microtime(true);
    }

    protected function log(string $sql): void
    {
        $this->sql .= $sql.PHP_EOL;
    }

    public function rollbackTransaction(): void
    {
        $this->log('ROLLBACK');
    }

    public function commitTransaction(): void
    {
        $this->log('COMMIT');
        $this->stopTimestamp = microtime(true);
    }

    public function query(string $sql)
    {
        $this->log($sql);
    }

    public function handleChanges(UnitOfWork $bag): void
    {
        if ($bag->isEmpty()) {
            return;
        }

        # INSERT
        $inserts = $bag->getData(ActionType::INSERT());
        foreach ($inserts as $table => $records) {
            foreach ($records as $hash => $data) {
                $this->insert($table, $data['columns'], $data['values']);
            }
        }

        # UPDATE
        $updates = $bag->getData(ActionType::UPDATE());
        foreach ($updates as $table => $records) {
            foreach ($records as $hash => $data) {
                $this->update($table, $data['where'], $data['columns'], $data['values']);
            }
        }

        # REMOVE
        $removes = $bag->getData(ActionType::DELETE());
        foreach ($removes as $table => $records) {
            foreach ($records as $idName => $data) {
                $this->delete($table, $data['where']);
            }
        }
    }
}
