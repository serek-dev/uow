<?php

namespace Unit\UnitOfWork;

use BaseTest;
use Generator;
use ReflectionClass;
use Stubs\PersistAbleStub;
use Stwarog\Uow\Exceptions\UnitOfWorkException;
use Stwarog\Uow\UnitOfWork\ActionType;
use Stwarog\Uow\UnitOfWork\PersistAble;
use Stwarog\Uow\UnitOfWork\UnitOfWork;
use Stwarog\Uow\UnitOfWork\VirtualEntity;

class UnitOfWorkTest extends BaseTest
{
    # insert

    /**
     * 0
     * @test
     */
    public function insert__few_times_same_object__will_insert_once(): void
    {
        // Given
        $mock = PersistAbleStub::create($this)->stub;

        // When
        $uow = new UnitOfWork();
        $this->assertFalse($uow->wasPersisted($mock));
        $uow->insert($mock);
        $uow->insert($mock);
        $uow->insert($mock);

        // Then
        $this->assertTrue($uow->has(ActionType::INSERT(), $mock));
        $this->assertFalse($uow->has(ActionType::UPDATE(), $mock));
        $this->assertFalse($uow->has(ActionType::DELETE(), $mock));
        $this->assertTrue($uow->wasPersisted($mock));
        $this->assertCount(1, $uow->getData(ActionType::INSERT()));
    }

    /**
     * @test
     * @dataProvider insert__same_data_setDataProvider
     *
     * @param PersistAble[] $mocks
     * @param int           $expectedCount
     */
    public function insert__same_data_set__will_make_one_entry(array $mocks, int $expectedCount): void
    {
        // When
        $uow = new UnitOfWork();
        foreach ($mocks as $mock) {
            $uow->insert($mock);
        }

        // Then
        $this->assertCount($expectedCount, $uow->getData(ActionType::INSERT()));
    }

    public function insert__same_data_setDataProvider(): array
    {
        return [
            [
                [PersistAbleStub::create($this)->columnValues(['a'], [1])->stub],
                1,
            ],
            [
                [
                    PersistAbleStub::create($this)->columnValues(['a'], [1])->stub,
                    PersistAbleStub::create($this)->columnValues(['a'], [1])->stub,
                ],
                1,
            ],
            [
                [
                    PersistAbleStub::create($this)->columnValues(['a'], [1])->stub,
                    PersistAbleStub::create($this)->columnValues(['a'], [1])->stub,
                    PersistAbleStub::create($this)->columnValues(['a'], [2])->stub,
                ],
                1,
            ],
            [
                [
                    PersistAbleStub::create($this, 'tab2')->columnValues(['a'], [1])->stub,
                    PersistAbleStub::create($this, 'tab2')->columnValues(['a'], [1])->stub,
                    PersistAbleStub::create($this, 'tab1')->columnValues(['a'], [2])->stub,
                ],
                2,
            ],
        ];
    }

    /** @test */
    public function insert__deleted_entity__throws_exception(): void
    {
        // Excepts
        $this->expectException(UnitOfWorkException::class);
        $this->expectExceptionMessageMatches('~but it was already marked as deleted.~');

        $mock = PersistAbleStub::create($this)->keys()->stub;

        // When
        $uow = new UnitOfWork();
        $uow->delete($mock);
        $uow->insert($mock);
    }


    /** @test */
    public function insert__changed_entity_after_pushing_it__it_reflects_changes(): void
    {
        // Given
        $mock = new VirtualEntity('table', ['a'], ['2']);
        $mock->set('a', '1');

        // When
        $uow = new UnitOfWork();
        $uow->insert($mock);

        $before = $uow->getData(ActionType::INSERT());

        $reflection = new ReflectionClass($mock);
        $p          = $reflection->getProperty('values');
        $p->setAccessible(true);
        $p->setValue($mock, ['changed']);

        $after = $uow->getData(ActionType::INSERT());

        // Then
        $this->assertNotSame($before, $after);
        $this->assertCount(1, $uow->getData(ActionType::INSERT()));
    }

    # update

    /** @test */
    public function update__has_no_id_key__throws_exception(): void
    {
        // Excepts
        $this->expectException(UnitOfWorkException::class);
        $this->expectExceptionMessageMatches('~but it has no primary key name specified.~');

        // Given
        $mock = PersistAbleStub::create($this)->stub;

        // When
        $uow = new UnitOfWork();
        $uow->update($mock);
    }

    /** @test */
    public function update__has_id_key_but_no_value__throws_exception(): void
    {
        // Excepts
        $this->expectException(UnitOfWorkException::class);
        $this->expectExceptionMessageMatches('~but it has no primary key value specified.~');

        // Given
        $mock = PersistAbleStub::create($this)->keys('id', null)->stub;

        // When
        $uow = new UnitOfWork();
        $uow->update($mock);
    }


    /** @test */
    public function update__deleted_entity__throw_exception(): void
    {
        // Excepts
        $this->expectException(UnitOfWorkException::class);
        $this->expectExceptionMessageMatches('~but it was already marked as deleted.~');

        // Given
        $mock = PersistAbleStub::create($this)->keys()->stub;

        // When
        $uow = new UnitOfWork();
        $uow->delete($mock);
        $uow->update($mock);
    }

    /** @test */
    public function update__entity__ok(): void
    {
        // Given
        $mock = PersistAbleStub::create($this)->keys()
            ->columnValues(['a', 'b', 'c'], ['1', '2', '3'])->stub;

        // When
        $uow = new UnitOfWork();
        $uow->update($mock);

        // Then
        $this->assertFalse($uow->has(ActionType::INSERT(), $mock));
        $this->assertTrue($uow->has(ActionType::UPDATE(), $mock));
        $this->assertFalse($uow->has(ActionType::DELETE(), $mock));
        $this->assertCount(1, $uow->getData(ActionType::UPDATE()));
    }

    # delete

    /** @test */
    public function delete__has_no_id_key__throws_exception(): void
    {
        // Excepts
        $this->expectException(UnitOfWorkException::class);
        $this->expectExceptionMessageMatches('~but it has no primary key name specified.~');

        // Given
        $mock = PersistAbleStub::create($this)->stub;

        // When
        $uow = new UnitOfWork();
        $uow->delete($mock);
    }

    /** @test */
    public function delete__has_id_key_but_no_value__throws_exception(): void
    {
        // Excepts
        $this->expectException(UnitOfWorkException::class);
        $this->expectExceptionMessageMatches('~but it has no primary key value specified.~');

        // Given
        $mock = PersistAbleStub::create($this)->keys('id', null)->stub;

        // When
        $uow = new UnitOfWork();
        $uow->delete($mock);
    }

    /** @test */
    public function delete__many_entities_at_once__ok(): void
    {
        // Given
        $mock1 = PersistAbleStub::create($this)->keys()->stub;
        $mock2 = PersistAbleStub::create($this)->keys()->stub;
        $mock3 = PersistAbleStub::create($this)->keys()->stub;

        // When
        $uow = new UnitOfWork();
        $uow->delete($mock1);
        $uow->delete($mock2);
        $uow->delete($mock3);

        // Then
        $this->assertTrue($uow->has(ActionType::DELETE(), $mock1));
        $this->assertTrue($uow->has(ActionType::DELETE(), $mock2));
        $this->assertTrue($uow->has(ActionType::DELETE(), $mock3));
        $this->assertCount(1, $uow->getData(ActionType::DELETE()));
    }

    # isEmpty

    /**
     * @test
     * @dataProvider provideIsEmptyReturnsExpectedValue
     */
    public function isEmpty__returns_expected_value(UnitOfWork $uow, bool $expected): void
    {
        $this->assertSame($uow->isEmpty(), $expected);
    }

    /**
     * @return Generator<string, array>
     */
    public function provideIsEmptyReturnsExpectedValue(): Generator
    {
        $entity = PersistAbleStub::create($this)->keys()->stub;
        $uow1 = new UnitOfWork();

        yield 'uow has no insert, update, delete should return true' => [
            'uow' => $uow1,
            'expected' => true
        ];

        $uow2 = new UnitOfWork();
        $uow2->insert($entity);

        yield 'uow has insert and no: update, delete should return false' => [
            'uow' => $uow2,
            'expected' => false
        ];

        $uow3 = new UnitOfWork();
        $uow3->update($entity);

        yield 'uow has update and no: insert, delete should return false' => [
            'uow' => $uow3,
            'expected' => false
        ];

        $uow4 = new UnitOfWork();
        $uow4->delete($entity);

        yield 'uow has delete and no: insert, update should return false' => [
            'uow' => $uow4,
            'expected' => false
        ];
    }

    # reset

    /** @test */
    public function reset__has_data__ok(): void
    {
        // Given
        $mock1 = PersistAbleStub::create($this)->keys()->stub;
        $mock2 = PersistAbleStub::create($this)->keys()->columnValues(['a'], ['1'])->stub;
        $mock3 = PersistAbleStub::create($this)->keys()->stub;

        // When
        $uow = new UnitOfWork();
        $uow->insert($mock1);
        $uow->update($mock2);
        $uow->delete($mock3);

        // Then
        $this->assertFalse($uow->isEmpty());
        $uow->reset();
        $this->assertTrue($uow->isEmpty());
    }

    # getData

    /** @test */
    public function getData__nothing_persisted__empty_array(): void
    {
        // When
        $uow = new UnitOfWork();

        // Then
        $this->assertEmpty($uow->getData(ActionType::DELETE()));
    }
}
