<?php
declare(strict_types=1);


namespace Stwarog\Uow;


interface DebugAble
{
    /**
     * Dumps MySql query details.
     * @return array
     *         [
     *              'sql' => (string) with all queries in Transaction
     *         ]
     */
    public function debug(): array;
}
