<?php


namespace Stwarog\Uow;


class AutoIncrementIdStrategy implements IdGenerationStrategyInterface
{
    public function handle(EntityInterface $entity, DBConnectionInterface $db): void
    {
        $entity->setId($db->nextAutoIncrementNo($entity->table(), $entity->idKey()));
    }
}
