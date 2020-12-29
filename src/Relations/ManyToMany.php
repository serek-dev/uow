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

namespace Stwarog\Uow\Relations;


use Iterator;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\UnitOfWork\VirtualEntity;

class ManyToMany implements RelationInterface, Iterator
{
    /** @var string */
    private $keyFrom;
    /** @var string */
    private $keyThroughFrom;
    /** @var string */
    private $tableThrough;
    /** @var string */
    private $keyThroughTo;
    /** @var string */
    private $modelTo;
    /** @var string */
    private $keyTo;
    /** @var EntityInterface[] */
    private $related = [];

    public function __construct(string $keyFrom, string $keyThroughFrom, string $tableThrough, string $keyThroughTo, string $modelTo, string $keyTo)
    {
        $this->keyFrom        = $keyFrom;
        $this->keyThroughFrom = $keyThroughFrom;
        $this->tableThrough   = $tableThrough;
        $this->keyThroughTo   = $keyThroughTo;
        $this->modelTo        = $modelTo;
        $this->keyTo          = $keyTo;
    }

    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        foreach ($this as $relatedEntity) {
            $parentEntity->addPostPersist(
                function (EntityInterface $parentEntity) use ($entityManager, $relatedEntity) {
                    $entityManager->persist($relatedEntity);
                    $virtualEntity = new VirtualEntity(
                        $this->tableThrough,
                        [$this->keyThroughFrom, $this->keyThroughTo],
                        [$parentEntity->get($this->keyFrom), $relatedEntity->get($this->keyTo)]
                    );
                    $entityManager->persist($virtualEntity);
                }
            );
        }
    }

    public function toArray(): array
    {
        $this->related;
    }

    public function isDirty(): bool
    {
        foreach ($this as $entity) {
            if ($entity->isDirty() || $entity->isNew()) {
                return true;
            }
        }

        return false;
    }

    public function isNew(): bool
    {
        foreach ($this as $entity) {
            if ($entity->isDirty()) {
                return false;
            }
        }

        return true;
    }

    public function setRelatedData(array $relatedEntities = []): void
    {
        $this->related = $relatedEntities;
    }

    public function isEmpty(): bool
    {
        return empty($this->related);
    }

    public function current()
    {
        return current($this->related);
    }

    public function next()
    {
        next($this->related);
    }

    public function key()
    {
        return key($this->related);
    }

    public function valid()
    {
        return key($this->related) !== null;
    }

    public function rewind()
    {
        reset($this->related);
    }
}
