<?php

namespace Unit\Relations;

use BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Relations\BelongsTo;

class BelongsToTest extends BaseTest
{
    /** @test */
    public function isEmpty__entity__skip(): void
    {
        // Given
        $relation      = new BelongsTo('from_id', 'table', 'to_id');
        /** @var EntityManagerInterface|MockObject $em */
        $em            = $this->createMock(EntityManagerInterface::class);
        /** @var EntityInterface|MockObject $relatedEntity */
        $relatedEntity = $this->createMock(EntityInterface::class);
        $em->expects($this->never())->method('persist');

        // When
        $relation->handleRelations($em, $relatedEntity);
    }

    /** @test */
    public function handleRelations__has_data__persist(): void
    {
        // Given
        $from  = 'from_id';
        $table = 'table';
        $to    = 'to_id';

        /** @var EntityInterface|MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);

        /** @var EntityInterface|MockObject $relatedEntity */
        $relatedEntity = $this->createMock(EntityInterface::class);
        $relatedEntity
            ->expects($this->exactly(2))
            ->method('get')
            ->with($to)
            ->willReturn(1);

        $relation = new BelongsTo($from, $table, $to);

        $entity
            ->expects($this->once())
            ->method('set')
            ->with($relation->keyFrom(), $relatedEntity->get($relation->keyTo()));

        $relation->setRelatedData([$relatedEntity]);

        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('persist')
            ->with($relatedEntity);

        // When
        $relation->handleRelations($em, $entity);
    }
}
