<?php

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
