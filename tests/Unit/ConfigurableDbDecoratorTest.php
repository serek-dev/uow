<?php

namespace Unit;

use BaseTest;
use Exception;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\ConfigurableDbDecorator;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\Exceptions\RuntimeUOWException;
use Stwarog\Uow\UnitOfWork\UnitOfWork;

/** @covers \Stwarog\Uow\ConfigurableDbDecorator */
final class ConfigurableDbDecoratorTest extends BaseTest
{
    /**
     * @dataProvider provideTransactionMethods
     * @test
     */
    public function handleTransaction__withConfig(string $method, bool $expected, array $config = []): void
    {
        // Given
        /** @var DBConnectionInterface&MockObject $db */
        $db = $this->createMock(DBConnectionInterface::class);

        $db->expects($this->exactly($expected ? 1 : 0))
            ->method($method);

        // And decorator with config
        $decorator = new ConfigurableDbDecorator($db, $config);

        // When
        $decorator->$method();
    }

    public function provideTransactionMethods(): Generator
    {
        yield 'startTransaction without transaction config key' => ['startTransaction', true, []];
        yield 'commitTransaction without transaction config key' => ['commitTransaction', true, []];
        yield 'rollbackTransaction without transaction config key' => ['rollbackTransaction', true, []];
        yield 'startTransaction with transaction config key set to true' => [
            'startTransaction',
            true,
            ['transaction' => true]
        ];
        yield 'commitTransaction with transaction config key set to true' => [
            'commitTransaction',
            true,
            ['transaction' => true]
        ];
        yield 'rollbackTransaction with transaction config key set to true' => [
            'rollbackTransaction',
            true,
            ['transaction' => true]
        ];
        yield 'startTransaction with transaction config key set to false' => [
            'startTransaction',
            false,
            ['transaction' => false]
        ];
        yield 'commitTransaction with transaction config key set to false' => [
            'commitTransaction',
            false,
            ['transaction' => false]
        ];
        yield 'rollbackTransaction with transaction config key set to false' => [
            'rollbackTransaction',
            false,
            ['transaction' => false]
        ];
    }

    /**
     * @dataProvider provideForeignKeysConfig
     * @test
     */
    public function handleForeignKeys__withConfig(bool $disableKeysCheck, array $config = []): void
    {
        // Given
        /** @var DBConnectionInterface&MockObject $db */
        $db = $this->createMock(DBConnectionInterface::class);

        $db->expects($this->exactly($disableKeysCheck ? 2 : 0))
            ->method('query')
            ->withConsecutive(
                ['SET FOREIGN_KEY_CHECKS=0;'],
                ['SET FOREIGN_KEY_CHECKS=1;']
            );

        // And decorator with config
        $decorator = new ConfigurableDbDecorator($db, $config);

        // When
        $decorator->handleChanges(new UnitOfWork());
    }

    public function provideForeignKeysConfig(): Generator
    {
        yield 'no config, handles with foreign keys check' => [false, []];
        yield 'config with true value, handles with foreign keys check' => [false, ['foreign_key_check' => true]];
        yield 'config with false value, handles without foreign keys check' => [true, ['foreign_key_check' => false]];
    }

    /**
     * @dataProvider provideDebugConfig
     * @test
     */
    public function debug__withConfig(array $config = []): void
    {
        // Given
        /** @var DBConnectionInterface&MockObject $db */
        $db = $this->createMock(DBConnectionInterface::class);
        $db->expects($this->once())->method('debug')->willReturn([]);

        // And decorator with config
        $decorator = new ConfigurableDbDecorator($db, $config);

        // When
        $decorator->debug();
    }

    public function provideDebugConfig(): Generator
    {
        yield 'no config, debug allowed' => [[]];
        yield 'config with value as true, debug allowed' => [['debug' => true]];
    }

    /** @test */
    public function debug__disabledInConfig__throwsException(): void
    {
        // Given
        /** @var DBConnectionInterface&MockObject $db */
        $db = $this->createMock(DBConnectionInterface::class);

        // And decorator with disabled debug in config
        $decorator = new ConfigurableDbDecorator($db, ['debug' => false]);

        // Then
        $this->expectException(RuntimeUOWException::class);
        $this->expectExceptionMessage('No debug config option enabled.');

        // When
        $decorator->debug();
    }
}
