<?php
declare(strict_types=1);


namespace Stwarog\Uow\IdGenerators;


use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;

class AutoIncrementIdStrategy extends AbstractGeneratorWithRequiredIdKeyStrategy implements
    IdGenerationStrategyInterface
{
    public function handle(EntityInterface $entity, DBConnectionInterface $db): void
    {
        $this->verifyHasIdKey($entity);
        $entity->setId($db->nextAutoIncrementNo($entity->table(), $entity->idKey()));
    }
}
