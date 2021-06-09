<?php
declare(strict_types=1);


namespace Stwarog\Uow\UnitOfWork;

use MyCLabs\Enum\Enum;

/**
 * @codeCoverageIgnore
 *
 * @method static ActionType SELECT()
 * @method static ActionType INSERT()
 * @method static ActionType UPDATE()
 * @method static ActionType DELETE()
 */
class ActionType extends Enum
{
    public const SELECT = 'select';
    public const INSERT = 'insert';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
}
