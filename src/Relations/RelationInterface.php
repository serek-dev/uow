<?php
declare(strict_types=1);


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\TouchAble;

interface RelationInterface extends InteractWithEntityManager, TouchAble
{
    /**
     * @return EntityInterface[]
     */
    public function toArray(): array;

    /**
     * Should contain wrapped original Models.
     *
     * @param EntityInterface[] $relatedEntities
     */
    public function setRelatedData(array $relatedEntities): void;
}
