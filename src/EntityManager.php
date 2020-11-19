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
        if ($entity->isNew()) {

            if (empty($entity->idValue())) {
                $entity->generateIdValue($this->db);
            }

            $this->uow->insert($entity);

//            $this->handleRelations($entity->relations(), $entity);

            return;
        }

        if (!$entity->isDirty()) {
            return;
        }

        $this->uow->update($entity);
//        $this->handleRelations($entity->relations());
    }

    protected function handleRelations(RelationBag $relationBag, ?EntityInterface $parentEntity): void
    {
        if ($relationBag->isEmpty() || $relationBag->isDirty() === false) {
            return;
        }

        foreach ($relationBag->getData() as $model) {
            $this->persist($model);
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
    }

    public function debug(): array
    {
        return $this->db->debug();
    }
}
