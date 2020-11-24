<?php

use Stwarog\Uow\Utils\ReflectionHelper;

if (!function_exists('_get')) {
    function _get(object $object, string $property)
    {
        return ReflectionHelper::getValue($object, $property);
    }
}
