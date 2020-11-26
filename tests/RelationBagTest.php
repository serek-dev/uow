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


use Stubs\PersistAbleStub;
use Stwarog\Uow\Exceptions\OutOfRangeUOWException;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Relations\RelationInterface;

class RelationBagTest extends BaseTest
{
    # isDirty

    /** @test */
    public function construct__initial_state(): void
    {
        $bag = new RelationBag();
        $this->assertTrue($bag->isEmpty());
        $this->assertFalse($bag->isDirty());
    }

    /** @test */
    public function isDirty__new_added__true(): void
    {
        $field = 'field';
        $bag = new RelationBag();
        $mock = $this->createMock(RelationInterface::class);

        $mock->expects($this->once())->method('isEmpty')->willReturn(false);
        $mock->expects($this->once())->method('isDirty')->willReturn(false);
        $mock->expects($this->once())->method('isNew')->willReturn(true);

        $bag->add($field, $mock);
        $this->assertTrue($bag->isDirty());
        $this->assertFalse($bag->isEmpty());
    }

    /** @test */
    public function isDirty__dirty_added__true(): void
    {
        $field = 'field';
        $bag = new RelationBag();
        $mock = $this->createMock(RelationInterface::class);

        $mock->expects($this->once())->method('isEmpty')->willReturn(false);
        $mock->expects($this->once())->method('isDirty')->willReturn(true);
        $mock->expects($this->never())->method('isNew');

        $bag->add($field, $mock);
        $this->assertTrue($bag->isDirty());
        $this->assertFalse($bag->isEmpty());
    }

    # add

    /** @test */
    public function add__empty__add_item_and_skip_dirty_checking(): void
    {
        $field = 'field';
        $bag = new RelationBag();
        $mock = $this->createMock(RelationInterface::class);

        $mock->expects($this->once())->method('isEmpty')->willReturn(true);
        $mock->expects($this->never())->method('isDirty');
        $mock->expects($this->never())->method('isNew');

        $bag->add($field, $mock);
        $this->assertFalse($bag->isDirty());
        $this->assertFalse($bag->isEmpty());
    }

    # get

    /** @test */
    public function get__existing_field(): void
    {
        $field = 'field';
        $bag = new RelationBag();
        $mock = $this->createStub(RelationInterface::class);

        $bag->add($field, $mock);
        $this->assertNotEmpty($bag->get($field));
    }

    /** @test */
    public function get__not_existing_field__throws_exception(): void
    {
        $field = 'test';
        $bag = new RelationBag();

        $this->expectException(OutOfRangeUOWException::class);
        $this->expectExceptionMessage('Unable to find field test in relation bag.');

        $bag->get($field);
    }

    # iterable / toArray

    /** @test */
    public function foreach__has_data(): void
    {
        $bag = new RelationBag();

        for ($c = 0; $c !== 5; $c++) {
            $mock = $this->createStub(RelationInterface::class);
            $bag->add('field' . $c, $mock);
        }

        $this->assertNotEmpty($bag);

        /** @var RelationInterface $item */
        foreach ($bag as $key => $item) {
            $this->assertInstanceOf(RelationInterface::class, $item);
        }
    }

    /** @test */
    public function toArray__has_data(): void
    {
        $bag = new RelationBag();

        for ($c = 0; $c !== 5; $c++) {
            $mock = $this->createStub(RelationInterface::class);
            $bag->add('field' . $c, $mock);
        }

        $data = $bag->toArray();
        $this->assertNotEmpty($data);
    }
}
