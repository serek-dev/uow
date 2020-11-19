<?php


namespace Stwarog\Uow\Fuel;


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

    public function save(Model $orm): void
    {
        $this->persist(new FuelModelAdapter($orm));
    }

    public function delete(Model $orm): void
    {
        $this->remove(new FuelModelAdapter($orm));
    }
}
