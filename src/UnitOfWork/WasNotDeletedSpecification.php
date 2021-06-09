<?php
declare(strict_types=1);


namespace Stwarog\Uow\UnitOfWork;


use Stwarog\Uow\Exceptions\UnitOfWorkException;

class WasNotDeletedSpecification implements UnitOfWorkSpecificationInterface
{
    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    public function __construct(UnitOfWork $unitOfWork)
    {
        $this->unitOfWork = $unitOfWork;
    }

    /**
     * @param PersistAble $entity
     *
     * @return bool
     * @throws UnitOfWorkException
     */
    public function isSatisfiedBy(PersistAble $entity): bool
    {
        if ($this->unitOfWork->has(ActionType::DELETE(), $entity)) {
            throw new UnitOfWorkException(
                sprintf(
                    'Attempted to persist entity <%s>, but it was already marked as deleted.',
                    get_class($entity->originalClass())
                )
            );
        }

        return true;
    }
}
