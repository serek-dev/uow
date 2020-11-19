<?php


namespace Stwarog\Uow;

use MyCLabs\Enum\Enum;

/**
 * @method static ActionType INSERT()
 * @method static ActionType UPDATE()
 * @method static ActionType DELETE()
 */
class ActionType extends Enum
{
    public const INSERT = 'insert';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
}
