<?php

namespace Unit\Relations;

use BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Relations\HasMany;
use Stwarog\Uow\Relations\ManyToMany;
use Stwarog\Uow\UnitOfWork\VirtualEntity;

class HasManyTest extends BaseTest
{
    /** @test */
    public function handleRelations_no_related_entities_skips(): void
    {
        // Given
        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);

        $em
            ->expects($this->never())
            ->method('persist');

        $relation = new HasMany('asd', 'asd', 'dsa');

        // When
        /** @var EntityInterface|MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);
        $relation->handleRelations($em, $entity);
    }

    /** @test */
    public function handleRelations__related_no_key_to_value__set_from_itself(): void
    {
        // Given entity manager
        $em = $this->createMock(EntityManagerInterface::class);

        // And parent entity
        $table = 'table';
        $columns = ['column'];
        $values = ['value'];
        $parentEntity = new VirtualEntity($table, $columns, $values);

        // And ManyToMany relation
        $keyFrom = 'key_from';
        $keyThroughFrom = 'key_through';
        $keyThroughTo = 'key_through_to';
        $tableThrough = 'table_through';
        $modelTo = 'model';
        $keyTo = 'key_to';
        $manyToMany = new ManyToMany($keyFrom, $keyThroughFrom, $tableThrough, $keyThroughTo, $modelTo, $keyTo);

        // With Related Entity
        $relatedEntity = new VirtualEntity('$table', [], []);
        $manyToMany->setRelatedData([$relatedEntity]);

        // Then first Entity Manager persist should store $relatedEntity
        $em->expects($this->at(0))
            ->method('persist')
            ->with($relatedEntity);

        // And second call should contains new VirtualEntity
        // with data from ManyToMany
        $em->expects($this->at(1))
            ->method('persist')
            ->willReturnCallback(function(VirtualEntity $newVirtualEntity) use (
                $keyFrom,
                $parentEntity,
                $relatedEntity,
                $keyThroughTo,
                $keyTo,
                $keyThroughFrom,
                $tableThrough
            ) {
                $this->assertSame($tableThrough, $newVirtualEntity->table());
                $expectedColumns = [$keyThroughFrom, $keyThroughTo];
                $this->assertSame($expectedColumns, $newVirtualEntity->columns());
                $this->assertSame($parentEntity->get($keyFrom), $relatedEntity->get($keyTo));
            });

        // When ManyToMany relations are handled (in Entity Manager irl)
        $manyToMany->handleRelations($em, $parentEntity);
        foreach ($parentEntity->getPostPersistClosures() as $closure) {
            $closure($parentEntity);
        }
    }
}
