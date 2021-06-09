<?php



namespace Unit\IdGenerators;


use BaseTest;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerators\NoIncrementIdStrategy;

class NoIncrementIdStrategyTest extends BaseTest
{
    /** @test */
    public function handle(): void
    {
        // Given
        $entity = $this->createMock(EntityInterface::class);
        $entity->expects($this->never())->method('setId');
        $entity->expects($this->never())->method('idKey');

        $db = $this->createMock(DBConnectionInterface::class);

        $strategy = new NoIncrementIdStrategy();

        // When
        $strategy->handle($entity, $db);
    }
}
