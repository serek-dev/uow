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

            $this->requestIdFor($entity);

            $this->handleRelationsOf($entity);

            $this->uow->insert($entity);

            return;
        }

        $this->handleRelationsOf($entity);

        if ($entity->isDirty() === false) {
            return;
        }

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

        foreach ($entity->relations()->toArray() as $field => $relation) {
            $relation->handleRelations($this, $entity);
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
