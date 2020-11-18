<?php


namespace Stwarog\Uow\Fuel;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;

class EntityManager implements EntityManagerInterface
{
    public function persist(EntityInterface $entity): void
    {
        // TODO: Implement persist() method.
    }

    public function delete(EntityInterface $entity): void
    {
        // TODO: Implement delete() method.
    }

    public function flush(): void
    {
        // TODO: Implement flush() method.
    }
}
