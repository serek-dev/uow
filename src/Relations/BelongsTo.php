<?php

declare(strict_types=1);

namespace Stwarog\Uow\Relations;

use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Shared\AbstractOneToOneRelation;

final class BelongsTo extends AbstractOneToOneRelation implements RelationInterface, HasRelationFromToSchema
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        if ($this->isEmpty()) {
            return;
        }
        $entityManager->persist($this->relatedEntity);
        $parentEntity->set($this->keyFrom(), $this->relatedEntity->get($this->keyTo()));
    }
}
