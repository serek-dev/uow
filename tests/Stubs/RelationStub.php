<?php



namespace Stubs;


use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stwarog\Uow\Relations\RelationInterface;

class RelationStub
{
    /** @var MockObject|RelationInterface */
    public $stub;

    public function __construct(TestCase $case, string $table = 'table_name')
    {
        $builder    = new MockBuilder($case, RelationInterface::class);
        $this->stub = $builder->getMock();
    }

    public static function create(TestCase $case): self
    {
        return new self($case);
    }
}
