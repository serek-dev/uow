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
use Stubs\PersistAbleStub;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManager;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Exceptions\OutOfRangeUOWException;
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
        $entity = $this->createMock(EntityInterface::class);
        $this->uow->expects($this->once())->method('wasPersisted')->willReturn(true);
        $entity->expects($this->never())->method('isNew');

        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_without_id__generates_id(): void
    {
        $entity = $this->createMock(EntityInterface::class);
        $this->uow->expects($this->once())->method('wasPersisted')->willReturn(false);
        $entity->expects($this->once())->method('isNew')->willReturn(true);
        $entity->expects($this->once())->method('idValue')->willReturn(null);
        $entity->expects($this->once())->method('generateIdValue')->with($this->db);
        $this->uow->expects($this->once())->method('insert')->with($entity);

        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_with_id__skip_generate_id(): void
    {
        $entity = $this->createMock(EntityInterface::class);
        $this->uow->expects($this->once())->method('wasPersisted')->willReturn(false);
        $entity->expects($this->once())->method('isNew')->willReturn(true);
        $entity->expects($this->once())->method('idValue')->willReturn('some_id');
        $entity->expects($this->never())->method('generateIdValue');
        $this->uow->expects($this->once())->method('insert')->with($entity);

        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_with_not_dirty_relations__skips(): void
    {
        $relations = $this->createMock(RelationBag::class);
        $relations->expects($this->once())->method('isDirty')->willReturn(false);

        $relationItem = $this->createMock(RelationInterface::class);
        $relationItem->expects($this->never())->method('handleRelations')->withAnyParameters();

        $relations->add('fake', $relationItem);

        $entity = $this->createMock(EntityInterface::class);
        $entity->expects($this->once())->method('relations')->willReturn($relations);

        $this->uow->expects($this->once())->method('wasPersisted')->willReturn(false);

        $entity->expects($this->once())->method('isNew')->willReturn(true);

        $this->uow->expects($this->once())->method('insert')->with($entity);

        $s = $this->service();
        $s->persist($entity);
    }

    /** @test */
    public function persist__new_with_dirty_relations__handles(): void
    {
        $s = $this->service();

        $relationBag = new RelationBag();
        $entity = $this->createMock(EntityInterface::class);

        $relationItem = $this->createMock(RelationInterface::class);
        $relationItem->expects($this->once())->method('handleRelations')
            ->with($s, $entity);
        $relationItem->expects($this->once())->method('isDirty')->willReturn(true);

        $relationBag->add('fake', $relationItem);

        $entity->expects($this->exactly(2))->method('relations')->willReturn($relationBag);

        $this->uow->expects($this->once())->method('wasPersisted')->willReturn(false);
        $entity->expects($this->once())->method('isNew')->willReturn(true);
        $this->uow->expects($this->once())->method('insert')->with($entity);

        $s->persist($entity);
    }

    # update

    /** @test */
    public function persist__not_new_dirty__updates(): void
    {
        $this->uow->method('wasPersisted')->willReturn(false);
        $entity = $this->createStub(EntityInterface::class);
        $entity->method('isNew')->willReturn(false);
        $entity->method('isDirty')->willReturn(true);

        $this->uow->expects($this->once())->method('update')
            ->with($entity);

        $s = $this->service();
        $s->persist($entity);
    }

    # remove

    /** @test */
    public function remove__entity(): void
    {
        $entity = $this->createStub(EntityInterface::class);
        $this->uow->expects($this->once())
            ->method('delete')->with($entity);

        $this->service()->remove($entity);
    }

    # debug

    /** @test */
    public function debug__no_option_given__shows_output(): void
    {
        $this->db->expects($this->once())->method('debug')->willReturn(['debug']);
        $output = $this->service(['debug' => true])->debug();
        $this->assertNotEmpty($output);
    }

    /** @test */
    public function debug__option_as_false__throws_exception(): void
    {
        $this->expectException(RuntimeUOWException::class);
        $this->expectExceptionMessage('No debug config option enabled.');
        $this->service(['debug' => false])->debug();
    }

    /** @test */
    public function debug__option_given__shows_output(): void
    {
        $this->db->expects($this->once())->method('debug')->willReturn(['debug']);
        $output = $this->service(['debug' => true])->debug();
        $this->assertNotEmpty($output);
    }

    # flush

    /** @test */
    public function flush__no_error__success(): void
    {
        $this->db->expects($this->once())->method('startTransaction');
        $this->db->expects($this->once())->method('handleChanges')
            ->with($this->uow);
        $this->db->expects($this->once())->method('commitTransaction');
        $this->uow->expects($this->once())->method('reset');
        $this->service()->flush();
    }

    /** @test */
    public function flush__exception_occurs__rethrow_it_and_rollbacks(): void
    {
        $this->expectException(Exception::class);
        $this->db->expects($this->once())->method('startTransaction');
        $this->db->expects($this->once())->method('handleChanges')
            ->with($this->uow)->willThrowException(new Exception());
        $this->db->expects($this->never())->method('commitTransaction');
        $this->db->expects($this->once())->method('rollbackTransaction');
        $this->uow->expects($this->never())->method('reset');
        $this->service()->flush();
    }

    private function service(array $config = []): EntityManagerInterface
    {
        return new EntityManager($this->db, $this->uow, $config);
    }
}
