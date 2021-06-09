<?php

declare(strict_types=1);

namespace Stwarog\Uow\Relations;

use InvalidArgumentException;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Shared\AbstractOneToOneRelation;

class HasOne extends AbstractOneToOneRelation implements InteractWithEntityManager, HasRelationFromToSchema
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        if ($this->isEmpty()) {
            return;
        }

        $parentEntity->addPostPersist(
            function (EntityInterface $parentEntity) use ($entityManager) {
                $relatedEntity = $this->relatedEntity;
                # todo: refactor! it doesn't have to be a valid related object
                # add inversion key checking
                /** @var BelongsTo[] $matchingRelatedEntityRelations */
                $relationData = $relatedEntity->relations()->toArray();
                $matchingRelatedEntityRelations = array_filter(
                    $relationData,
                    function (RelationInterface $relatedRelation) {
                        return $relatedRelation instanceof BelongsTo;
                    }
                );

                if (empty($matchingRelatedEntityRelations)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'No BelongsTo inversion of HasOne has been found in %s.',
                            get_class($relatedEntity->originalClass())
                        )
                    );
                }

                $belongsToRelation = reset($matchingRelatedEntityRelations);

                $relatedEntity->set($belongsToRelation->keyFrom(), $parentEntity->get($this->keyFrom()));
                $entityManager->persist($relatedEntity);
            }
        );
    }
}
