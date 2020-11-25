<?php
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

namespace Stwarog\Uow\Shared;


use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\Relations\RelationInterface;

abstract class AbstractOneToOneRelation implements RelationInterface
{
    /** @var string */
    protected $keyFrom;
    /** @var string */
    protected $tableTo;
    /** @var string */
    protected $keyTo;
    /** @var EntityInterface */
    protected $relatedEntity;

    public function __construct(string $keyFrom, string $tableTo, string $keyTo)
    {
        $this->keyFrom = $keyFrom;
        $this->tableTo = $tableTo;
        $this->keyTo   = $keyTo;
    }

    public function keyFrom(): string
    {
        return $this->keyFrom;
    }

    public function tableTo(): string
    {
        return $this->tableTo;
    }

    public function keyTo(): string
    {
        return $this->keyTo;
    }

    public function toArray(): array
    {
        return [$this->relatedEntity];
    }

    public function isDirty(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->relatedEntity->isDirty();
    }

    public function isEmpty(): bool
    {
        return empty($this->relatedEntity);
    }

    public function isNew(): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        return $this->relatedEntity->isNew();
    }

    public function setRelatedData(array $relatedEntities = []): void
    {
        if (empty($relatedEntities)) {
            return;
        }
        $this->relatedEntity = reset($relatedEntities);
    }
}
