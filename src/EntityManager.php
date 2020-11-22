<?php


namespace Stwarog\Uow;

use Exception;

class EntityManager implements EntityManagerInterface
{
    /** @var DBConnectionInterface */
    private $db;
    /** @var UnitOfWork */
    private $uow;

    public function __construct(DBConnectionInterface $db)
    {
        $this->db  = $db;
        $this->uow = new UnitOfWork();
    }

    public function persist(EntityInterface $entity): void
    {
        if ($this->uow->wasPersisted($entity)) {
            return;
        }

        if ($entity->isNew()) {

            if (empty($entity->idValue())) {
                $entity->generateIdValue($this->db);
            }

            $relations = $entity->relations();

            # belongs to
            if ($relations->isDirty()) {
                foreach ($relations->toArray() as $field => $relation) {
                    $relatedEntity = $relation->getObject();
                    $this->persist($relatedEntity);
                    $entity->set($relation->keyFrom(), $relatedEntity->get($relation->keyTo()));
                }
            }

            $this->uow->insert($entity);

            return;
        }

        if (!$entity->isDirty()) {
            return;
        }

        $this->uow->update($entity);
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
