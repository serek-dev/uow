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

namespace Stwarog\Uow\UnitOfWork;


use InvalidArgumentException;

class UnitOfWork
{
    private $data = [];
    private $persistedHashes = [];

    public function insert(PersistAble $entity): void
    {
        $this->mark($entity);

        $table   = $entity->table();
        $columns = $entity->columns();
        $values  = $entity->values();

        $hash = $this->hash($columns);

        $valuesAggregate   = $this->data[ActionType::INSERT][$table][$hash]['values'] ?? [];
        $valuesAggregate[] = $values;

        $this->data[ActionType::INSERT][$table][$hash] = [
            'columns' => $columns,
            'values'  => $valuesAggregate,
        ];
    }

    private function mark(PersistAble $entity): void
    {
        $this->persistedHashes[] = $this->objectHash($entity);
    }

    private function objectHash(PersistAble $entity): string
    {
        return _id($entity);
    }

    private function hash(array $array): string
    {
        return serialize($array);
    }

    public function update(PersistAble $entity): void
    {
        $this->mark($entity);

        if (empty($entity->idValue())) {
            throw new InvalidArgumentException('Cant update entity when no idKey specified.');
        }

        $table   = $entity->table();
        $columns = $entity->columns();
        $values  = $entity->values();
        $id      = $entity->idValue();
        $idKey   = $entity->idKey();

        $hash = $this->hash(array_combine($columns, $values));

        $idsAggregate   = $this->data[ActionType::UPDATE][$table][$hash]['where'][0][2] ?? [];
        $idsAggregate[] = $id;

        $this->data[ActionType::UPDATE][$table][$hash] = [
            'where'   => [
                [$idKey, 'IN', $idsAggregate],
            ],
            'columns' => $columns,
            'values'  => $values,
        ];
    }

    public function getData(ActionType $type): array
    {
        if (empty($this->data[(string) $type])) {
            return [];
        }

        return $this->data[(string) $type];
    }

    public function delete(PersistAble $entity)
    {
        $this->mark($entity);

        $table   = $entity->table();
        $idName  = $entity->idKey();
        $idValue = $entity->idValue();
        $idKey   = $entity->idKey();

        $hash = $this->hash([$idName]);

        $idsAggregate   = $this->data[ActionType::DELETE][$table][$hash]['where'][0][2] ?? [];
        $idsAggregate[] = $idValue;

        $this->data[ActionType::DELETE][$table][$hash] = [
            'where' => [
                [$idKey, 'IN', $idsAggregate],
            ],
        ];
    }

    public function wasPersisted(PersistAble $entity): bool
    {
        return in_array($this->objectHash($entity), $this->persistedHashes);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function reset(): void
    {
        $this->data            = [];
        $this->persistedHashes = [];
    }
}
