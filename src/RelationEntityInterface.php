<?php


namespace Stwarog\Uow;


interface RelationEntityInterface extends EntityInterface
{
    public function parent(): EntityInterface;

    public function setParent(EntityInterface $entity): void;
}
