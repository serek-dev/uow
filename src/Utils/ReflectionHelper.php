<?php

declare(strict_types=1);

namespace Stwarog\Uow\Utils;

use ReflectionClass;
use ReflectionException;

class ReflectionHelper
{
    /**
     * @param        $object
     * @param string $property
     *
     * @return mixed
     * @throws ReflectionException
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
