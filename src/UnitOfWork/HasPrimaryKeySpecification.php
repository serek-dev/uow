<?php

declare(strict_types=1);

namespace Stwarog\Uow\UnitOfWork;

use Stwarog\Uow\Exceptions\UnitOfWorkException;

final class HasPrimaryKeySpecification implements UnitOfWorkSpecificationInterface
{
    /**
     * @param PersistAble $entity
     *
     * @return bool
     * @throws UnitOfWorkException
     */
    public function isSatisfiedBy(PersistAble $entity): bool
    {
        if (empty($entity->idKey())) {
            throw new UnitOfWorkException(
                sprintf(
                    'Attempted to update entity <%s>, but it has no primary key name specified.',
                    get_class($entity->originalClass())
                )
            );
        }

        if (empty($entity->idValue())) {
            throw new UnitOfWorkException(
                sprintf(
                    'Attempted to update entity <%s>, but it has no primary key value specified.',
                    get_class($entity->originalClass())
                )
            );
        }

        return true;
    }
}
