<?php


namespace Stwarog\Uow;


use MyCLabs\Enum\Enum;

/**
 * @method static RelationType HAS_ONE()
 * @method static RelationType BELONGS_TO()
 * @method static RelationType HAS_MANY()
 * @method static RelationType MANY_TO_MANY()
 */
class RelationType extends Enum
{
    public const HAS_ONE = '_has_one';
    public const BELONGS_TO = '_belongs_to';
    public const HAS_MANY = '_has_many';
    public const MANY_TO_MANY = '_many_many';
}
