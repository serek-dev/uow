<?php
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


namespace Unit\UnitOfWork;


use BaseTest;
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
        $this->assertSame(1, count($uow->getData(ActionType::INSERT())));
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
        $this->assertSame($expectedCount, count($uow->getData(ActionType::INSERT())));
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
        $this->assertSame(1, count($uow->getData(ActionType::INSERT())));
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
        $this->assertSame(1, count($uow->getData(ActionType::UPDATE())));
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
        $this->assertSame(1, count($uow->getData(ActionType::DELETE())));
    }

    # isEmpty

    /**
     * 0
     * @test
     */
    public function isEmpty__nothing_was_persisted__true(): void
    {
        // When
        $uow = new UnitOfWork();

        // Then
        $this->assertTrue($uow->isEmpty());
    }

    /** @test */
    public function isEmpty__was_persisted__false(): void
    {
        // When
        $uow = new UnitOfWork();
        $uow->insert(PersistAbleStub::create($this)->stub);

        // Then
        $this->assertFalse($uow->isEmpty());
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
