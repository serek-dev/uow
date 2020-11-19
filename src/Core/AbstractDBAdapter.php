<?php


namespace Stwarog\Uow\Core;


use Stwarog\Uow\ActionType;
use Stwarog\Uow\ChangesBag;
use Stwarog\Uow\DBConnectionInterface;
use const PHP_EOL;

abstract class AbstractDBAdapter implements DBConnectionInterface
{
    protected $sql = '';

    public function debug(): array
    {
        $debug['sql'] = $this->sql;

        foreach (ActionType::keys() as $type) {
            $debug[$type] = substr_count($this->sql, $type);
        }

        return $debug;
    }

    public function startTransaction(): void
    {
        $this->sql = '';
        $this->log('START TRANSACTION');
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
    }

    public function query(string $sql): void
    {
        $this->log($sql);
    }

    public function handleChanges(ChangesBag $bag): void
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
