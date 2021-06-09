<?php

declare(strict_types=1);

namespace Stwarog\Uow\Relations;

use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Shared\AbstractHasManyRelation;

class HasMany extends AbstractHasManyRelation implements RelationInterface, HasRelationFromToSchema
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        foreach ($this as $relatedEntity) {
            $parentEntity->addPostPersist(
                function (EntityInterface $parentEntity) use ($relatedEntity, $entityManager) {
                    if (empty($relatedEntity->get($this->keyTo))) {
                        $relatedEntity->set($this->keyTo, $parentEntity->get($this->keyFrom));
                    }
                    $entityManager->persist($relatedEntity);
                }
            );
        }
    }
}
