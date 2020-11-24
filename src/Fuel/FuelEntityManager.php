<?php


namespace Stwarog\Uow\Fuel;


use Exception;
use Fuel\Core\DB;
use Orm\Model;
use Stwarog\Uow\EntityManager;
use Stwarog\Uow\EntityManagerInterface;

class FuelEntityManager extends EntityManager implements EntityManagerInterface
{
    public static function initialize(DB $db): self
    {
        return new self(new FuelDBAdapter($db));
    }

    /**
     * @param Model $orm
     * @param bool  $flush
     *
     * @throws Exception
     */
    public function save(Model $orm, bool $flush = false): void
    {
        $this->persist(new FuelModelAdapter($orm));
        if ($flush) {
            $this->flush();
        }
    }

    public function delete(Model $orm): void
    {
        $this->remove(new FuelModelAdapter($orm));
    }
}
