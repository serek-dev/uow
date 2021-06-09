<?php

declare(strict_types=1);

namespace Stwarog\Uow\UnitOfWork;

use Stwarog\Uow\Exceptions\UnitOfWorkException;

interface UnitOfWorkSpecificationInterface
{
    /**
     * Should throws exception on failure and return true on success.
     *
     * @param PersistAble $entity
     *
     * @return bool
     * @throws UnitOfWorkException
     */
    public function isSatisfiedBy(PersistAble $entity): bool;
}
