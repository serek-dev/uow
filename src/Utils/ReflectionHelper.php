<?php

declare(strict_types=1);

namespace Stwarog\Uow\Utils;

use ReflectionClass;
use ReflectionException;

final class ReflectionHelper
{
    /**
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    public static function getValue($object, string $property)
    {
        $reflection = new ReflectionClass($object);
        if ($reflection->hasProperty($property) === false) {
            return null;
        }

        $p = $reflection->getProperty($property);
        $p->setAccessible(true);

        return $p->getValue($object);
    }
}
