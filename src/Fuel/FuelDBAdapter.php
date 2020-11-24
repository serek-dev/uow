<?php


namespace Stwarog\Uow\Fuel;


use Fuel\Core\Database_Query_Builder_Delete;
use Fuel\Core\Database_Query_Builder_Update;
use Fuel\Core\Database_Query_Builder_Where;
use Fuel\Core\Database_Result;
use Fuel\Core\DB;
use Stwarog\Uow\Shared\AbstractDBAdapter;
use Stwarog\Uow\DBConnectionInterface;

class FuelDBAdapter extends AbstractDBAdapter implements DBConnectionInterface
{
    /** @var DB */
    private $db;

    private $cachedTableIds = [];

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

    public function query(string $sql)
    {
        parent::log($sql);

        return $this->db::query($sql)->execute();
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

    public function nextAutoIncrementNo(string $table, string $idKey = 'id'): string
    {
        if (isset($this->cachedTableIds[$table])) {
            $this->cachedTableIds[$table][$idKey]++;

            return (string) $this->cachedTableIds[$table][$idKey];
        }

        /** @var Database_Result $result */
        $result = $this->db::select($this->db::expr('MAX('.$idKey.') as count'))->from($table)->execute();
        parent::log($this->db::last_query());

        $this->cachedTableIds[$table][$idKey] = (int) $result->get('count') + 1;

        return (string) $this->cachedTableIds[$table][$idKey];
    }
}
