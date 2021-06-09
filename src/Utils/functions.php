<?php

declare(strict_types=1);

use Stwarog\Uow\Utils\ReflectionHelper;

if (!function_exists('_get')) {
    function _get($object, string $property)
    {
        return ReflectionHelper::getValue($object, $property);
    }
}
