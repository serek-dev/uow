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


namespace Unit\IdGenerators;


use BaseTest;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\Exceptions\MissingIdKeyUOWException;
use Stwarog\Uow\IdGenerators\UuidIdStrategy;

class UuidIdStrategyTest extends BaseTest
{
    /** @test */
    public function handle__no_idKey__throws_exception(): void
    {
        // Except
        $this->expectException(MissingIdKeyUOWException::class);
        $this->expectExceptionMessageMatches('~Attempted to generate primary key for model~');

        // Given
        $entity = $this->createMock(EntityInterface::class);
        $entity->expects($this->never())->method('setId');
        $entity->expects($this->once())->method('idKey')->willReturn('');

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

        $entity = $this->createMock(EntityInterface::class);
        $entity->expects($this->once())->method('idKey')->willReturn($idKey);
        $entity->expects($this->once())->method('setId')->withAnyParameters();
        $entity->expects($this->never())->method('table');

        $db = $this->createMock(DBConnectionInterface::class);

        $strategy = new UuidIdStrategy();

        // When
        $strategy->handle($entity, $db);
    }
}
