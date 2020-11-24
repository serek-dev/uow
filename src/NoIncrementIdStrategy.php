<?php


namespace Stwarog\Uow;


class NoIncrementIdStrategy implements IdGenerationStrategyInterface
{
    public function handle(EntityInterface $entity, DBConnectionInterface $db): void
    {
        return;
    }
}
