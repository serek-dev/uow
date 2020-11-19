<?php


namespace Stwarog\Uow\Fuel;


use MyCLabs\Enum\Enum;

/**
 * @method static FuelRelationType HAS_ONE()
 * @method static FuelRelationType BELONGS_TO()
 * @method static FuelRelationType HAS_MANY()
 * @method static FuelRelationType MANY_TO_MANY()
 */
class FuelRelationType extends Enum
{
    public const HAS_ONE = '_has_one';
    public const BELONGS_TO = '_belongs_to';
    public const HAS_MANY = '_has_many';
    public const MANY_TO_MANY = '_many_many';
}
