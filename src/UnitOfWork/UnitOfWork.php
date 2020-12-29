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


class UnitOfWork
{
    /**
     * Unified and compiled (expected) set of values for DbConnection
     *
     * @var array
     */
    private $data = [];

    # all of these 3 below, has to be unique (index = unique id)

    /** @var PersistAble[] */
    private $insert = [];
    /** @var PersistAble[] */
    private $update = [];
    /** @var PersistAble[] */
    private $delete = [];

    public function insert(PersistAble $entity): void
    {
        (new WasNotDeletedSpecification($this))->isSatisfiedBy($entity);
        $this->insert[$entity->objectHash()] = $entity;
    }

    public function update(PersistAble $entity): void
    {
        (new HasPrimaryKeySpecification())->isSatisfiedBy($entity);
        (new WasNotDeletedSpecification($this))->isSatisfiedBy($entity);
        $this->update[$entity->objectHash()] = $entity;
    }

    public function has(ActionType $type, PersistAble $entity): bool
    {
        return isset($this->$type[$entity->objectHash()]);
    }

    public function getData(ActionType $type): array
    {
        $this->compile($type);

        if (empty($this->data[(string) $type])) {
            return [];
        }

        return $this->data[(string) $type];
    }

    private function compile(ActionType $type)
    {
        $this->data[(string) $type] = [];

        if ($type->equals(ActionType::INSERT())) {
            foreach ($this->insert as $id => $entity) {
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

            return;
        }

        if ($type->equals(ActionType::UPDATE())) {
            foreach ($this->update as $id => $entity) {
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

            return;
        }

        # nothing left, so it is DELETE
        foreach ($this->delete as $id => $entity) {
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
    }

    private function hash(array $array): string
    {
        return serialize($array);
    }

    public function delete(PersistAble $entity)
    {
        (new HasPrimaryKeySpecification())->isSatisfiedBy($entity);
        $this->delete[$entity->objectHash()] = $entity;
    }

    public function wasPersisted(PersistAble $entity): bool
    {
        $hash = $entity->objectHash();

        return isset($this->insert[$hash]) || isset($this->update[$hash]);
    }

    public function isEmpty(): bool
    {
        return empty($this->insert) && empty ($this->update && empty($this->delete));
    }

    public function reset(): void
    {
        $this->insert = $this->update = $this->delete = $this->data = [];
    }
}
