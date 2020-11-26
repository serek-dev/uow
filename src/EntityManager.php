<?php declare(strict_types=1);
/*
    Copyright (c) 2020 Sebastian Twaróg <contact@stwarog.com>

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Stwarog\Uow;

use Exception;
use Stwarog\Uow\UnitOfWork\UnitOfWork;

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

            $this->uow->insert($entity);

            $this->handleRelationsOf($entity);

            return;
        }

        if ($entity->isDirty()) {
            $this->uow->update($entity);
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

        foreach ($entity->relations() as $field => $relation) {
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
