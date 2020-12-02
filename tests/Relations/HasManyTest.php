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


namespace Relations;


use BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Relations\BelongsTo;
use Stwarog\Uow\Relations\HasMany;

class HasManyTest extends BaseTest
{
    /** @test */
    public function handleRelations_no_related_entities_skips(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');

        $relation = new HasMany('asd', 'asd', 'dsa');
        $relation->handleRelations($em, $this->createStub(EntityInterface::class));
    }

    /** @test */
    public function handleRelations__related_no_key_to_value__set_from_itself(): void
    {
        $this->markTestSkipped();
        $from = 'from_id';
        $table = 'table';
        $to = 'to_id';

        #                                                   here
        # $relatedEntity->set($this->keyTo, $parentEntity->get($this->keyFrom));

        $parentEntityFrom = 1;

        $parentEntity = $this->createMock(EntityInterface::class);
        $parentEntity->expects($this->once())->method('get')
            ->with($from)->willReturn($parentEntityFrom);

        $relation = new HasMany($from, $table, $to);
        $relatedEntity = $this->createMock(EntityInterface::class);
        $relatedEntity->expects($this->once())->method('set')->with($to, $parentEntityFrom);
        $relation->setRelatedData([$relatedEntity]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');

        $relation->setRelatedData([$relatedEntity]);
        $relation->handleRelations($em, $parentEntity);
    }
}
