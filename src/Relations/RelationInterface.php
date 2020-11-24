<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\TouchAble;

interface RelationInterface extends InteractWithEntityManager, TouchAble
{
    /**
     * @return EntityInterface[]
     */
    public function toArray(): array;
}
