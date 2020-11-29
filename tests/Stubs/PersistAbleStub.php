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


namespace Stubs;


use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Stwarog\Uow\UnitOfWork\PersistAble;

class PersistAbleStub
{
    /** @var MockObject|PersistAble */
    public $stub;

    public function __construct(TestCase $case, string $table = 'table_name')
    {
        $builder    = new MockBuilder($case, PersistAble::class);
        $this->stub = $builder->getMock();
        $this->stub->method('originalClass')->willReturnSelf();
        $this->stub->method('table')->willReturn($table);
        $this->stub->method('objectHash')->willReturn(Uuid::uuid4()->toString());
    }

    public static function create(TestCase $case, string $table = 'table_name'): self
    {
        return new self($case, $table);
    }

    public function keys(?string $idKey = 'id', ?string $idValue = '1'): self
    {
        $this->stub->method('idKey')->willReturn($idKey);
        $this->stub->method('idValue')->willReturn($idValue);

        return $this;
    }

    public function columnValues(array $columns = ['a', 'b'], array $values = ['1', 5]): self
    {
        $this->stub->method('columns')->willReturn($columns);
        $this->stub->method('values')->willReturn($values);

        return $this;
    }
}
