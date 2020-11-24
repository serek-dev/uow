<?php


namespace Stwarog\Uow;


class AutoIncrementIdStrategy implements IdGenerationStrategyInterface
{
    public function handle(EntityInterface $entity, DBConnectionInterface $db): void
    {
        if ($entity->idKey() === null) {
            return;
        }
        $entity->setId($db->nextAutoIncrementNo($entity->table(), $entity->idKey()));
    }
}
