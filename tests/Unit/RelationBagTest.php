<?php

namespace Unit;

use BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\Exceptions\OutOfRangeUOWException;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Relations\RelationInterface;

/** @covers \Stwarog\Uow\RelationBag */
final class RelationBagTest extends BaseTest
{
    # isDirty

    /**
     * 0
     * @test
     */
    public function construct__initial_state(): void
    {
        // Given & When
        $bag = new RelationBag();

        // Then
        $this->assertTrue($bag->isEmpty());
        $this->assertFalse($bag->isDirty());
    }

    /** @test */
    public function isDirty__new_added__true(): void
    {
        // Given
        $field = 'field';
        /** @var RelationInterface|MockObject $mock */
        $mock  = $this->createMock(RelationInterface::class);

        $mock
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);

        $mock
            ->expects($this->once())
            ->method('isDirty')
            ->willReturn(false);

        $mock
            ->expects($this->once())
            ->method('isNew')
            ->willReturn(true);

        // When
        $bag = new RelationBag();
        $bag->add($field, $mock);

        // Then
        $this->assertTrue($bag->isDirty());
        $this->assertFalse($bag->isEmpty());
    }

    /** @test */
    public function isDirty__dirty_added__true(): void
    {
        // Given
        $field = 'field';
        /** @var RelationInterface|MockObject $mock */
        $mock  = $this->createMock(RelationInterface::class);

        $mock
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(false);

        $mock
            ->expects($this->once())
            ->method('isDirty')
            ->willReturn(true);

        $mock
            ->expects($this->never())
            ->method('isNew');

        // When
        $bag = new RelationBag();
        $bag->add($field, $mock);

        // Then
        $this->assertTrue($bag->isDirty());
        $this->assertFalse($bag->isEmpty());
    }

    # add

    /** @test */
    public function add__empty__add_item_and_skip_dirty_checking(): void
    {
        // Given
        $field = 'field';
        /** @var RelationInterface|MockObject $mock */
        $mock  = $this->createMock(RelationInterface::class);

        $mock
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $mock
            ->expects($this->never())
            ->method('isDirty');

        $mock
            ->expects($this->never())
            ->method('isNew');

        // When
        $bag = new RelationBag();
        $bag->add($field, $mock);

        // Then
        $this->assertFalse($bag->isDirty());
        $this->assertFalse($bag->isEmpty());
    }

    # get

    /** @test */
    public function get__existing_field(): void
    {
        // Given
        $field = 'field';
        /** @var RelationInterface|MockObject $mock */
        $mock  = $this->createMock(RelationInterface::class);

        // When
        $bag = new RelationBag();
        $bag->add($field, $mock);

        // Then
        $this->assertNotEmpty($bag->get($field));
    }

    /** @test */
    public function get__not_existing_field__throws_exception(): void
    {
        // Excepts
        $this->expectException(OutOfRangeUOWException::class);
        $this->expectExceptionMessage('Unable to find field test in relation bag.');

        // Given
        $field = 'test';

        // When
        $bag = new RelationBag();
        $bag->get($field);
    }

    # iterable / toArray

    /** @test */
    public function foreach__has_data(): void
    {
        // Given
        $bag = new RelationBag();

        // When
        for ($c = 0; $c !== 5; $c++) {
            /** @var RelationInterface|MockObject $mock */
            $mock = $this->createMock(RelationInterface::class);
            $bag->add('field' . $c, $mock);
        }

        /** @var RelationInterface $item */
        foreach ($bag as $key => $item) {
            $this->assertInstanceOf(RelationInterface::class, $item);
        }

        // Then
        $this->assertNotEmpty($bag);
    }

    /** @test */
    public function toArray__has_data(): void
    {
        // Given
        $bag = new RelationBag();

        for ($c = 0; $c !== 5; $c++) {
            /** @var RelationInterface|MockObject $mock */
            $mock = $this->createMock(RelationInterface::class);
            $bag->add('field' . $c, $mock);
        }

        // When
        $data = $bag->toArray();

        // Then
        $this->assertNotEmpty($data);
    }
}
