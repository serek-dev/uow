<?php

declare(strict_types=1);

namespace Stwarog\Uow;

use Exception;
use Stwarog\Uow\Exceptions\RuntimeUOWException;
use Stwarog\Uow\UnitOfWork\UnitOfWork;

class EntityManager implements EntityManagerInterface
{
    private DBConnectionInterface $db;
    private UnitOfWork $uow;

    /**
     * EntityManager constructor.
     * @param DBConnectionInterface $db
     * @param UnitOfWork $uow
     * @param array<string, mixed> $config
     */
    public function __construct(DBConnectionInterface $db, UnitOfWork $uow, array $config = [])
    {
        $this->db = new ConfigurableDbDecorator($db, $config);
        $this->uow = $uow;
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

            $this->handlePostPersistClosures($entity);

            return;
        }

        $this->handleRelationsOf($entity);

        if ($entity->isDirty()) {
            $this->uow->update($entity);
        }

        $this->handlePostPersistClosures($entity);
    }

    private function requestIdFor(EntityInterface $entity): void
    {
        if (empty($entity->idValue())) {
            $entity->generateIdValue($this->db);
        }
    }

    private function handleRelationsOf(EntityInterface $entity): void
    {
        if (false === $entity->relations()->isDirty() || $entity->relations()->isEmpty()) {
            return;
        }

        foreach ($entity->relations() as $field => $relation) {
            $relation->handleRelations($this, $entity);
        }
    }

    private function handlePostPersistClosures(EntityInterface $entity): void
    {
        foreach ($entity->getPostPersistClosures() as $closure) {
            $closure($entity);
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
        if ($this->uow->isEmpty()) {
            return;
        }

        $this->db->startTransaction();
        try {
            $this->db->handleChanges($this->uow);
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
            throw $e;
        } finally {
            $this->uow->reset();
        }
        $this->db->commitTransaction();
    }

    /**
     * @return array<string, mixed>
     */
    public function debug(): array
    {
        return $this->db->debug();
    }
}
