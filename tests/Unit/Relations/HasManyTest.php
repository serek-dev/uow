<?php

namespace Unit\Relations;

use BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Relations\HasMany;

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
        $this->markTestSkipped();
        // @phpstan-ignore-next-line
        $from  = 'from_id';
        $table = 'table';
        $to    = 'to_id';

        #                                                   here
        # $relatedEntity->set($this->keyTo, $parentEntity->get($this->keyFrom));

        $parentEntityFrom = 1;

        /** @var EntityInterface|MockObject $parentEntity */
        $parentEntity = $this->createMock(EntityInterface::class);
        $parentEntity->expects($this->once())->method('get')
            ->with($from)->willReturn($parentEntityFrom);

        $relation      = new HasMany($from, $table, $to);
        /** @var EntityInterface|MockObject $relatedEntity */
        $relatedEntity = $this->createMock(EntityInterface::class);
        $relatedEntity->expects($this->once())->method('set')->with($to, $parentEntityFrom);
        $relation->setRelatedData([$relatedEntity]);

        /** @var EntityManagerInterface|MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');

        $relation->setRelatedData([$relatedEntity]);
        $relation->handleRelations($em, $parentEntity);
    }
}
