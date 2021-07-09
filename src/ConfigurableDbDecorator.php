<?php

declare(strict_types=1);

namespace Stwarog\Uow;

use Stwarog\Uow\UnitOfWork\UnitOfWork;

final class ConfigurableDbDecorator implements DBConnectionInterface
{
    /** @var DBConnectionInterface */
    private $db;

    /** @var array<string, mixed> */
    private $config;

    public function __construct(DBConnectionInterface $db, array $config = [])
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function startTransaction(): void
    {
        if (!$this->isTransactionEnabled()) {
            return;
        }
        $this->db->startTransaction();
    }

    public function rollbackTransaction(): void
    {
        if (!$this->isTransactionEnabled()) {
            return;
        }
        $this->db->rollbackTransaction();
    }

    public function commitTransaction(): void
    {
        if (!$this->isTransactionEnabled()) {
            return;
        }
        $this->db->commitTransaction();
    }

    public function insert(string $tableName, array $columns, array $values): void
    {
        $this->db->insert($tableName, $columns, $values);
    }

    public function update(string $tableName, array $where, array $columns, array $values): void
    {
        $this->db->update($tableName, $where, $columns, $values);
    }

    public function delete(string $tableName, array $where): void
    {
        $this->db->delete($tableName, $where);
    }

    public function query(string $sql): void
    {
        $this->db->query($sql);
    }

    public function handleChanges(UnitOfWork $bag): void
    {
        if ($this->handleForeignKeys()) {
            $this->db->handleChanges($bag);
            return;
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->db->handleChanges($bag);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function nextAutoIncrementNo(string $table, string $idKey = 'id'): string
    {
        return $this->db->nextAutoIncrementNo($table, $idKey);
    }

    public function debug(): array
    {
        return $this->db->debug();
    }

    private function isTransactionEnabled(): bool
    {
        return $this->config['transaction'] ?? true;
    }

    private function handleForeignKeys(): bool
    {
        return $this->config['foreign_key_check'] ?? true;
    }
}
