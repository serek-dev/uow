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
        $from = 'key_from';
        $keyThrough = 'key_through';
        $tableThrough = 'table_through';
        $modelTo = 'model';
        $keyTo = 'key_to';
        $manyToMany = new ManyToMany($from, $keyThrough, $tableThrough, $keyThrough, $modelTo, $keyTo);

        // With Related Entity
        $relatedEntity = new VirtualEntity('$table', [], []);
        $manyToMany->setRelatedData([$relatedEntity]);

        // Then
        $em->expects($this->at(0))
            ->method('persist')
            ->with($relatedEntity);

        $em->expects($this->at(1))
            ->method('persist')
            ->willReturnCallback(function(VirtualEntity $newVirtualEntity) use (
                $tableThrough
            ) {
                $this->assertSame($tableThrough, $newVirtualEntity->table());
            });

        // When
        $manyToMany->handleRelations($em, $parentEntity);
        foreach ($parentEntity->getPostPersistClosures() as $closure) {
            $closure($parentEntity);
        }
    }
}
