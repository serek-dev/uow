<?php
declare(strict_types=1);


namespace Stwarog\Uow\IdGenerators;


use Ramsey\Uuid\Uuid;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;

class UuidIdStrategy extends AbstractGeneratorWithRequiredIdKeyStrategy implements IdGenerationStrategyInterface
{
    public function handle(EntityInterface $entity, DBConnectionInterface $db): void
    {
        $this->verifyHasIdKey($entity);
        $entity->setId(Uuid::uuid4()->toString());
    }
}
