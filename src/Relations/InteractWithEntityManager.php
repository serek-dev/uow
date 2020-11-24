<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;

interface InteractWithEntityManager
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $entity): void;
}
