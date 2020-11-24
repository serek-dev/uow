<?php

use PHPUnit\Framework\TestCase;
use Stwarog\Uow\ActionType;
use Stwarog\Uow\UnitOfWork;

class ChangesBagTest extends TestCase
{
    /** @test */
    public function insert__various_entities__ok(): void
    {
        $table1 = 'table_name1';
        $columns1 = ['col1', 'col2'];
        $values1 = [12, 'asd'];

        $table2 = 'table_name1';
        $columns2 = ['col12', 'col12', 'col3'];
        $values2 = ['hello', 'asd diff', 'third one'];

        $table3 = 'table_name2';
        $columns3 = ['col12', 'col12'];
        $values3 = ['hello', 'asd'];

        $bag = new UnitOfWork();

        $bag->insert($table1, $columns1, $values1);
        $bag->insert($table1, $columns1, $values1);
        $bag->insert($table2, $columns2, $values2);
        $bag->insert($table3, $columns3, $values3);

        $inserts = $bag->getData(ActionType::INSERT());

        $this->assertArrayHasKey($table1, $inserts);
        $this->assertArrayHasKey($table2, $inserts);

        foreach ($inserts as $table => $records) {
            print($table . PHP_EOL);
            foreach ($records as $hash => $data) {
//                print($hash . PHP_EOL);
//                print_r($data);
            }
        }

        # todo test data structure
    }

    /** @test */
    public function removes__various_entities__ok(): void
    {
        $table1 = 'table_name1';
        $ids1 = range(1, 5);
        $table2 = 'table_name2';
        $ids2 = 2;

        $bag = new UnitOfWork();
        foreach ($ids1 as $id) {
            $bag->delete($table1, 'id', $id);
        }
        $bag->delete($table2, 'id', $ids2);

        $removes = $bag->getData(ActionType::DELETE());
        $this->assertTrue(true);
//        print_r($removes);
    }

    /** @test */
    public function updates__various_entities__ok(): void
    {
        $table1 = 'table_name1';
        $id1 = rand(1, 5);
        $columns1 = ['col1', 'col2'];
        $values1 = [12, 'asd'];

        $id2 = rand(6, 8);
        $values2 = [12, 'asd'];

        $bag = new UnitOfWork();

        $bag->update($table1, $id1, $columns1, $values1);
        $bag->update($table1, $id2, $columns1, $values2);
        $bag->update('table_name2', $id2, $columns1, $values2);
        $bag->update('table_name2', $id2, $columns1, [123, 123]);

        $data = $bag->getData(ActionType::UPDATE());
        print_r($data);
    }
}
