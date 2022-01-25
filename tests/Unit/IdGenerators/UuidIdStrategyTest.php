<?php

namespace Unit\IdGenerators;

use BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\Exceptions\MissingIdKeyUOWException;
use Stwarog\Uow\IdGenerators\UuidIdStrategy;

/** @covers \Stwarog\Uow\IdGenerators\UuidIdStrategy */
final class UuidIdStrategyTest extends BaseTest
{
    /** @test */
    public function handle__no_idKey__throws_exception(): void
    {
        // Except
        $this->expectException(MissingIdKeyUOWException::class);
        $this->expectExceptionMessageMatches('~Attempted to generate primary key for model~');

        // Given
        /** @var EntityInterface|MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);
        $entity->method('originalClass')->willReturnSelf();
        $entity->expects($this->never())->method('setId');
        $entity->expects($this->once())->method('idKey')->willReturn('');

        /** @var DBConnectionInterface|MockObject $db */
        $db = $this->createMock(DBConnectionInterface::class);

        $strategy = new UuidIdStrategy();

        // When
        $strategy->handle($entity, $db);
    }

    /** @test */
    public function handle__idKey_defined__calls_db(): void
    {
        // Given
        $idKey = 'main_id';

        /** @var EntityInterface|MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);
        $entity->method('originalClass')->willReturnSelf();
        $entity->expects($this->once())->method('idKey')->willReturn($idKey);
        $entity->expects($this->once())->method('setId')->withAnyParameters();
        $entity->expects($this->never())->method('table');

        /** @var DBConnectionInterface|MockObject $db */
        $db = $this->createMock(DBConnectionInterface::class);

        $strategy = new UuidIdStrategy();

        // When
        $strategy->handle($entity, $db);
    }
}
