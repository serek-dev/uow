<?php


namespace Stwarog\Uow;


use Exception;

class EntityManager implements EntityManagerInterface
{
    /** @var DBConnectionInterface */
    private $db;

    /** @var ChangesBag */
    private $bag;

    public function __construct(DBConnectionInterface $db)
    {
        $this->db  = $db;
        $this->bag = new ChangesBag();
    }

    public function persist(EntityInterface $entity): void
    {
        if ($entity->isNew()) {
            $this->bag->insert($entity);

            return;
        }

        if (!$entity->isDirty()) {
            return;
        }

        $this->bag->update($entity);
    }

    public function remove(EntityInterface $entity): void
    {
        $this->bag->delete($entity);
    }

    /**
     * @throws Exception
     */
    public function flush(): void
    {
        $this->db->startTransaction();
        try {
            $this->db->handleChanges($this->bag);
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
