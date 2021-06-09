<?php
declare(strict_types=1);


namespace Stwarog\Uow\IdGenerators;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\Exceptions\MissingIdKeyUOWException;

abstract class AbstractGeneratorWithRequiredIdKeyStrategy implements IdGenerationStrategyInterface
{
    protected function verifyHasIdKey(EntityInterface $entity): void
    {
        if (empty($entity->idKey())) {
            throw new MissingIdKeyUOWException(
                sprintf(
                    'Attempted to generate primary key for model %s, using %s, but no idKey (name) found.',
                    get_class($entity->originalClass()),
                    get_called_class()
                )
            );
        }
    }
}
