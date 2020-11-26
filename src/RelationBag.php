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

namespace Stwarog\Uow;


use Iterator;
use Stwarog\Uow\Exceptions\OutOfRangeUOWException;
use Stwarog\Uow\Relations\RelationInterface;

class RelationBag implements Iterator
{
    private $data = [];
    private $isDirty = false;

    public function add(string $field, RelationInterface $relation): void
    {
        $this->data[$field] = $relation;

        if ($relation->isEmpty()) {
            return;
        }

        if ($relation->isDirty() || $relation->isNew()) {
            $this->isDirty = true;
        }
    }

    public function get(string $field): RelationInterface
    {
        if (isset($this->data[$field]) === false) {
            throw new OutOfRangeUOWException(sprintf('Unable to find field %s in relation bag.', $field));
        }

        return $this->data[$field];
    }

    /**
     * @return array|RelationInterface[]
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    public function current()
    {
        return current($this->data);
    }

    public function next()
    {
        next($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function valid()
    {
        return key($this->data) !== null;
    }

    public function rewind()
    {
        reset($this->data);
    }
}
