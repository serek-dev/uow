<?php


namespace Stwarog\Uow\Relations;


use InvalidArgumentException;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;

class HasOne extends AbstractRelation implements InteractWithEntityManager, HasRelationFromToSchema
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $entity): void
    {
        $relatedEntity = $this->getObject();

        # todo: refactor! it doesn't have to be a valid related object

        /** @var AbstractRelation[] $matchingRelatedEntityRelations */
        $matchingRelatedEntityRelations = array_filter(
            $relatedEntity->relations()->toArray(),
            function (AbstractRelation $relatedRelation) {
                return $relatedRelation instanceof BelongsTo;
            }
        );

        if (empty($matchingRelatedEntityRelations)) {
            throw new InvalidArgumentException(
                sprintf('No BelongsTo inversion of HasOne has been found in %s.', get_class($relatedEntity->originalClass()))
            );
        }

        $belongsToRelation = reset($matchingRelatedEntityRelations);

        $relatedEntity->set($belongsToRelation->keyFrom(), $entity->get($this->keyFrom()));
        $entityManager->persist($relatedEntity);
    }
}
