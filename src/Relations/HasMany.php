<?php


namespace Stwarog\Uow\Relations;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Shared\AbstractHasManyRelation;

class HasMany extends AbstractHasManyRelation implements RelationInterface, HasRelationFromToSchema
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        foreach ($this->toArray() as $relatedEntity) {
            if (empty($relatedEntity->get($this->keyTo))) {
                $relatedEntity->set($this->keyTo, $parentEntity->get($this->keyFrom));
            }
            $entityManager->persist($relatedEntity);
        }
    }
}
