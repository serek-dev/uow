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


use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManager;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Exceptions\RuntimeUOWException;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Relations\RelationInterface;
use Stwarog\Uow\UnitOfWork\UnitOfWork;

class EntityManagerTest extends BaseTest
{
    /** @var MockObject|DBConnectionInterface */
    private $db;
    /** @var MockObject|UnitOfWork */
    private $uow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db  = $this->createMock(DBConnectionInterface::class);
        $this->uow = $this->createMock(UnitOfWork::class);
    }

    /** @test */
    public function persist__already_persisted__skips(): void
    {
        // Given
        $entity = $this->createMock(EntityInterface::class);
        $this->uow
            ->expects($this->once())
            ->method('wasPersisted')
            ->willReturn(true);

        $entity
            ->expects($this->never())
            ->method('isNew');

        // When
        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_without_id__generates_id(): void
    {
        // Given
        $entity = $this->createMock(EntityInterface::class);

        $this->uow
            ->expects($this->once())
            ->method('wasPersisted')
            ->willReturn(false);

        $entity
            ->expects($this->once())
            ->method('isNew')
            ->willReturn(true);

        $entity
            ->expects($this->once())
            ->method('idValue')
            ->willReturn(null);

        $entity
            ->expects($this->once())
            ->method('generateIdValue')
            ->with($this->db);
        $this->uow
            ->expects($this->once())
            ->method('insert')
            ->with($entity);

        // When
        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_with_id__skip_generate_id(): void
    {
        // Given
        $entity = $this->createMock(EntityInterface::class);

        $this->uow
            ->expects($this->once())
            ->method('wasPersisted')
            ->willReturn(false);

        $entity
            ->expects($this->once())
            ->method('isNew')
            ->willReturn(true);

        $entity
            ->expects($this->once())
            ->method('idValue')
            ->willReturn('some_id');

        $entity
            ->expects($this->never())
            ->method('generateIdValue');

        $entity
            ->expects($this->once())
            ->method('getPostPersistClosures');

        $this->uow
            ->expects($this->once())
            ->method('insert')
            ->with($entity);

        // When
        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_with_not_dirty_relations__skips(): void
    {
        // Given
        $relations = $this->createMock(RelationBag::class);
        $relations
            ->expects($this->once())
            ->method('isDirty')
            ->willReturn(false);

        $relationItem = $this->createMock(RelationInterface::class);
        $relationItem
            ->expects($this->never())
            ->method('handleRelations')
            ->withAnyParameters();

        $relations->add('fake', $relationItem);

        $entity = $this->createMock(EntityInterface::class);
        $entity
            ->expects($this->once())
            ->method('relations')
            ->willReturn($relations);

        $this->uow
            ->expects($this->once())
            ->method('wasPersisted')
            ->willReturn(false);

        $entity
            ->expects($this->once())
            ->method('isNew')
            ->willReturn(true);

        $this->uow
            ->expects($this->once())
            ->method('insert')
            ->with($entity);

        // When
        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_with_dirty_relations__handles(): void
    {
        // Given
        $s = $this->service();

        $relationBag = new RelationBag();
        $entity      = $this->createMock(EntityInterface::class);

        $relationItem = $this->createMock(RelationInterface::class);
        $relationItem
            ->expects($this->once())
            ->method('handleRelations')
            ->with($s, $entity);

        $relationItem
            ->expects($this->once())
            ->method('isDirty')
            ->willReturn(true);

        $relationBag->add('fake', $relationItem);

        $entity
            ->expects($this->exactly(3))
            ->method('relations')
            ->willReturn($relationBag);

        $this->uow
            ->expects($this->once())
            ->method('wasPersisted')
            ->willReturn(false);

        $entity
            ->expects($this->once())
            ->method('isNew')
            ->willReturn(true);

        $this->uow
            ->expects($this->once())
            ->method('insert')
            ->with($entity);

        // When
        $s->persist($entity);
    }

    # update

    /** @test */
    public function persist__not_new_dirty__updates(): void
    {
        // Given
        $this->uow
            ->method('wasPersisted')
            ->willReturn(false);

        $entity = $this->createMock(EntityInterface::class);
        $entity
            ->method('isNew')
            ->willReturn(false);

        $entity
            ->method('isDirty')
            ->willReturn(true);

        $this->uow
            ->expects($this->once())
            ->method('update')
            ->with($entity);

        // When
        $s = $this->service();
        $s->persist($entity);
    }

    # remove

    /** @test */
    public function remove__entity(): void
    {
        // Given
        $entity = $this->createMock(EntityInterface::class);
        $this->uow
            ->expects($this->once())
            ->method('delete')
            ->with($entity);

        // When
        $this->service()->remove($entity);
    }

    # debug

    /** @test */
    public function debug__no_option_given__shows_output(): void
    {
        // Given
        $config = ['debug' => true];
        $this->db
            ->expects($this->once())
            ->method('debug')
            ->willReturn(['debug']);

        // Then
        $output = $this->service($config)->debug();
        $this->assertNotEmpty($output);
    }

    /** @test */
    public function debug__option_as_false__throws_exception(): void
    {
        // Excepts
        $this->expectException(RuntimeUOWException::class);
        $this->expectExceptionMessage('No debug config option enabled.');

        // Given
        $config = ['debug' => false];

        // When
        $this->service($config)->debug();
    }

    /** @test */
    public function debug__option_given__shows_output(): void
    {
        // Given
        $config = ['debug' => true];
        $this->db
            ->expects($this->once())
            ->method('debug')
            ->willReturn(['debug']);

        // When
        $output = $this->service($config)->debug();

        // Then
        $this->assertNotEmpty($output);
    }

    # flush

    /** @test */
    public function flush__no_error__success(): void
    {
        // Given
        $this->db
            ->expects($this->once())
            ->method('startTransaction');

        $this->db
            ->expects($this->once())
            ->method('handleChanges')
            ->with($this->uow);

        $this->db
            ->expects($this->once())
            ->method('commitTransaction');

        $this->uow
            ->expects($this->once())
            ->method('reset');

        // When
        $this->service()->flush();
    }

    /** @test */
    public function flush__exception_occurs__rethrow_it_and_rollbacks(): void
    {
        // Excepts
        $this->expectException(Exception::class);

        // Given
        $this->db
            ->expects($this->once())
            ->method('startTransaction');

        $this->db
            ->expects($this->once())
            ->method('handleChanges')
            ->with($this->uow)
            ->willThrowException(new Exception());

        $this->db
            ->expects($this->never())
            ->method('commitTransaction');

        $this->db
            ->expects($this->once())
            ->method('rollbackTransaction');

        $this->uow
            ->expects($this->once())
            ->method('reset');

        // When
        $this->service()->flush();
    }

    # config

    /** @test */
    public function foreignKeysCheck__by_default__is_true(): void
    {
        // Given
        $this->db
            ->expects($this->never())
            ->method('query');

        // When
        $this->service()->flush();
    }

    /** @test */
    public function foreignKeysCheck__config_true_runs_queries_to_disable_it(): void
    {
        // Given
        $config = ['foreign_key_check' => false];
        $this->db
            ->expects($this->exactly(2))
            ->method('query')
            ->withConsecutive(['SET FOREIGN_KEY_CHECKS=0;'], ['SET FOREIGN_KEY_CHECKS=1;']);

        // When
        $this->service($config)->flush();
    }

    /** @test */
    public function flush__nothing_persisted__skips(): void
    {
        // Given
        $this->uow
            ->expects($this->once())
            ->method('isEmpty')
            ->willReturn(true);

        $this->db
            ->expects($this->never())
            ->method('startTransaction');

        // When
        $this->service()->flush();
    }

    private function service(array $config = []): EntityManagerInterface
    {
        return new EntityManager($this->db, $this->uow, $config);
    }
}
