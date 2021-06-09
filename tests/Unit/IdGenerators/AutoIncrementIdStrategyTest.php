<?php



namespace Unit\IdGenerators;


use BaseTest;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\Exceptions\MissingIdKeyUOWException;
use Stwarog\Uow\IdGenerators\AutoIncrementIdStrategy;

class AutoIncrementIdStrategyTest extends BaseTest
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
        $db->expects($this->never())->method('nextAutoIncrementNo');

        $strategy = new AutoIncrementIdStrategy();

        // When
        $strategy->handle($entity, $db);
    }

    /** @test */
    public function handle__idKey_defined__calls_db(): void
    {
        // Given
        $newId = (string) rand(1, 10);
        $table = 'table';
        $idKey = 'main_id';

        $entity = $this->createMock(EntityInterface::class);
        $entity->expects($this->exactly(2))->method('idKey')->willReturn($idKey);
        $entity->expects($this->once())->method('setId')->with($newId);
        $entity->expects($this->once())->method('table')->willReturn($table);

        $db = $this->createMock(DBConnectionInterface::class);
        $db->expects($this->once())->method('nextAutoIncrementNo')->willReturn($newId);
        $strategy = new AutoIncrementIdStrategy();

        // When
        $strategy->handle($entity, $db);
    }
}
