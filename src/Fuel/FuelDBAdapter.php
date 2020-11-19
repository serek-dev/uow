<?php


namespace Stwarog\Uow\Fuel;


use Fuel\Core\Database_Query_Builder_Delete;
use Fuel\Core\Database_Query_Builder_Update;
use Fuel\Core\Database_Query_Builder_Where;
use Fuel\Core\DB;
use Stwarog\Uow\Core\AbstractDBAdapter;
use Stwarog\Uow\DBConnectionInterface;

class FuelDBAdapter extends AbstractDBAdapter implements DBConnectionInterface
{
    /** @var DB */
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function startTransaction(): void
    {
        $this->db::start_transaction();
        parent::startTransaction();
    }

    public function rollbackTransaction(): void
    {
        $this->db::rollback_transaction();
        parent::rollbackTransaction();
    }

    public function commitTransaction(): void
    {
        $this->db::commit_transaction();
        parent::commitTransaction();
    }

    public function insert(string $tableName, array $columns, array $values): void
    {
        $statement = $this->db::insert($tableName);
        $statement->columns($columns);
        $statement->values($values);
        $this->query($statement->compile());
    }

    public function query(string $sql): void
    {
        $this->db::query($sql)->execute();
        parent::log($sql);
    }

    public function update(string $tableName, array $where, array $columns, array $values): void
    {
        /** @var Database_Query_Builder_Update|Database_Query_Builder_Where $statement */
        $statement = $this->db::update($tableName);
        $statement->set(array_combine($columns, $values));
        $statement->where($where);
        $this->query($statement->compile());
    }

    public function delete(string $tableName, array $where): void
    {
        /** @var Database_Query_Builder_Delete|Database_Query_Builder_Where $statement */
        $statement = $this->db::delete($tableName);
        $statement->where($where);
        $this->query($statement->compile());
    }
}
