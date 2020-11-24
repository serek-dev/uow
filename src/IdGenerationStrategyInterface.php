<?php


namespace Stwarog\Uow;


interface IdGenerationStrategyInterface
{
    public function handle(EntityInterface $entity, DBConnectionInterface $db): void;
}
