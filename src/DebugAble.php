<?php

declare(strict_types=1);

namespace Stwarog\Uow;

interface DebugAble
{
    /**
     * Dumps MySql query details.
     * @return array<string, mixed>&array{sql: string}
     */
    public function debug(): array;
}
