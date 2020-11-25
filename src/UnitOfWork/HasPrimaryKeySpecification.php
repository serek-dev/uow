<?php declare(strict_types=1);
/*
    Copyright (c) 2020 Sebastian Twaróg <contact@stwarog.com>

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Stwarog\Uow\UnitOfWork;


use Stwarog\Uow\Exceptions\UnitOfWorkException;

class HasPrimaryKeySpecification implements UnitOfWorkSpecificationInterface
{
    /**
     * @param PersistAble $entity
     *
     * @return bool
     * @throws UnitOfWorkException
     */
    public function isSatisfiedBy(PersistAble $entity): bool
    {
        if (empty($entity->idKey())) {
            throw new UnitOfWorkException(
                'Attempted to update entity <%s>, but it has no primary key name specified.',
                get_class($entity->originalClass())
            );
        }

        if (empty($entity->idValue())) {
            throw new UnitOfWorkException(
                'Attempted to update entity <%s>, but it has no primary key value specified.',
                get_class($entity->originalClass())
            );
        }

        return true;
    }
}
