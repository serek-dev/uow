<?php


namespace Stwarog\Uow;

use Exception;
use InvalidArgumentException;
use Stwarog\Uow\Relations\AbstractRelation;
use Stwarog\Uow\Relations\BelongsTo;
use Stwarog\Uow\Relations\HasOne;

class EntityManager implements EntityManagerInterface
{
    /** @var DBConnectionInterface */
    private $db;
    /** @var UnitOfWork */
    private $uow;

    public function __construct(DBConnectionInterface $db)
    {
        $this->db = $db;
        $this->uow = new UnitOfWork();
    }

    public function persist(EntityInterface $entity): void
    {
        if ($this->uow->wasPersisted($entity)) {
            return;
        }

        if ($entity->isNew()) {

            $this->requestIdFor($entity);

            $this->handleRelationsOf($entity);

            $this->uow->insert($entity);

            return;
        }

        if ($entity->isDirty() === false) {
            return;
        }

        $this->handleRelationsOf($entity);

        $this->uow->update($entity);
    }

    private function requestIdFor(EntityInterface $entity): void
    {
        if (empty($entity->idValue())) {
            $entity->generateIdValue($this->db);
        }
    }

    private function handleRelationsOf(EntityInterface $entity): void
    {
        if ($entity->relations()->isDirty() === false) {
            return;
        }

        # belongs to
        foreach ($entity->relations()->toArray() as $field => $relation) {
            $relatedEntity = $relation->getObject();

            if ($relation instanceof BelongsTo) {

                if (empty($relatedEntity)) {
                    continue;
                }

                $this->persist($relatedEntity);
                $entity->set($relation->keyFrom(), $relatedEntity->get($relation->keyTo()));
            }

            if ($relation instanceof HasOne) {

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

                $relatedEntity->set($belongsToRelation->keyFrom(), $entity->get($relation->keyFrom())); # Model_User
                $this->persist($relatedEntity);
            }
        }
    }

    public function remove(EntityInterface $entity): void
    {
        $this->uow->delete($entity);
    }

    /**
     * @throws Exception
     */
    public function flush(): void
    {
        $this->db->startTransaction();
        try {
            $this->db->handleChanges($this->uow);
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
            throw $e;
        }
        $this->db->commitTransaction();
        $this->uow->reset();
    }

    public function debug(): array
    {
        return $this->db->debug();
    }
}
