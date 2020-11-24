<?php


namespace Stwarog\Uow;


use Ramsey\Uuid\Uuid;

class UuidIdStrategy implements IdGenerationStrategyInterface
{
    public function handle(EntityInterface $entity, DBConnectionInterface $db): void
    {
        if ($entity->idKey() === null) {
            return;
        }
        $entity->setId(Uuid::uuid4());
    }
}
