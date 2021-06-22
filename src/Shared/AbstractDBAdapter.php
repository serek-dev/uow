<?php

declare(strict_types=1);

namespace Stwarog\Uow\Shared;

use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\UnitOfWork\ActionType;
use Stwarog\Uow\UnitOfWork\UnitOfWork;

use const PHP_EOL;

abstract class AbstractDBAdapter implements DBConnectionInterface
{
    /** @var string $sql */
    protected $sql = '';
    /** @var float $startTimestamp */
    protected $startTimestamp;
    /** @var float $stopTimestamp */
    protected $stopTimestamp;

    /**
     * @return array<string, mixed>
     */
    public function debug(): array
    {
        $debug['sql'] = $this->sql;
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
        $this->sql .= $sql . PHP_EOL;
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

    public function query(string $sql): void
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
