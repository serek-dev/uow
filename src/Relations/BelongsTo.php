<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;

class BelongsTo extends AbstractRelation implements RelationInterface, HasRelationFromToSchema
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $entity): void
    {
        if ($this->isEmpty()) {
            return;
        }
        $relatedEntity = $this->toArray()[0];
        $entityManager->persist($relatedEntity);
        $entity->set($this->keyFrom(), $relatedEntity->get($this->keyTo()));
    }
}
