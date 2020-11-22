<?php


namespace Stwarog\Uow;

use Exception;

class EntityManager implements EntityManagerInterface
{
    /** @var DBConnectionInterface */
    private $db;
    /** @var UnitOfWork */
    private $uow;

    private $skipIdGenerationValue = [];

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

            $relations = $entity->relations();

            if ($relations->hasRelations(RelationType::BELONGS_TO())) {
                $entities = $relations->get(RelationType::BELONGS_TO());
                foreach ($entities as $relatedEntity) {
                    $this->persist($relatedEntity);
                    $entity->handleBelongsTo($relatedEntity);
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
    }

    public function debug(): array
    {
        return $this->db->debug();
    }
}
