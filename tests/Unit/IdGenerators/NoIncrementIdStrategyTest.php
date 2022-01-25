<?php

namespace Unit\IdGenerators;

use BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerators\NoIncrementIdStrategy;

/** @covers \Stwarog\Uow\IdGenerators\NoIncrementIdStrategy */
final class NoIncrementIdStrategyTest extends BaseTest
{
    /** @test */
    public function handle(): void
    {
        // Given
        /** @var EntityInterface|MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);
        $entity->expects($this->never())->method('setId');
        $entity->expects($this->never())->method('idKey');

        /** @var DBConnectionInterface|MockObject $db */
        $db = $this->createMock(DBConnectionInterface::class);

        $strategy = new NoIncrementIdStrategy();

        // When
        $strategy->handle($entity, $db);
    }
}
