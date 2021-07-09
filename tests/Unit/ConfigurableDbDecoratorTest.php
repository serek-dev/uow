<?php

namespace Unit;

use BaseTest;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Stwarog\Uow\ConfigurableDbDecorator;
use Stwarog\Uow\DBConnectionInterface;

class ConfigurableDbDecoratorTest extends BaseTest
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
}
