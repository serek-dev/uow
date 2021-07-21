<?php

declare(strict_types=1);

namespace Stwarog\Uow\UnitOfWork;

class UnitOfWork
{
    /**
     * Unified and compiled (expected) set of values for DbConnection
     * @var mixed
     */
    private $data = [];

    # all of these 3 below, has to be unique (index = unique id)

    /** @var array<PersistAble> */
    private $insert = [];
    /** @var array<PersistAble> */
    private $update = [];
    /** @var array<PersistAble> */
    private $delete = [];

    public function insert(PersistAble $entity): void
    {
        (new WasNotDeletedSpecification($this))->isSatisfiedBy($entity);
        $this->insert[$entity->objectHash()] = $entity;
        $entity->noLongerNew();
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

    /**
     * @param ActionType $type
     * @return mixed
     * @phpstan-ignore-next-line todo
     */
    public function getData(ActionType $type): array
    {
        $this->compile($type);

        if (empty($this->data[(string)$type])) {
            return [];
        }

        return $this->data[(string)$type];
    }

    private function compile(ActionType $type): void
    {
        $this->data[(string)$type] = [];

        if ($type->equals(ActionType::INSERT())) {
            foreach ($this->insert as $id => $entity) {
                $table = $entity->table();
                $columns = $entity->columns();
                $values = $entity->values();

                $hash = $this->hash($columns);

                $valuesAggregate = $this->data[ActionType::INSERT][$table][$hash]['values'] ?? [];
                $valuesAggregate[] = $values;

                $this->data[ActionType::INSERT][$table][$hash] = [
                    'columns' => $columns,
                    'values' => $valuesAggregate,
                ];
            }

            return;
        }

        if ($type->equals(ActionType::UPDATE())) {
            foreach ($this->update as $id => $entity) {
                $table = $entity->table();
                $columns = $entity->columns();
                $values = $entity->values();
                $id = $entity->idValue();
                $idKey = $entity->idKey();

                $hash = $this->hash((array)array_combine($columns, $values));

                $idsAggregate = $this->data[ActionType::UPDATE][$table][$hash]['where'][0][2] ?? [];
                $idsAggregate[] = $id;

                $this->data[ActionType::UPDATE][$table][$hash] = [
                    'where' => [
                        [$idKey, 'IN', $idsAggregate],
                    ],
                    'columns' => $columns,
                    'values' => $values,
                ];
            }

            return;
        }

        # nothing left, so it is DELETE
        foreach ($this->delete as $id => $entity) {
            $table = $entity->table();
            $idName = $entity->idKey();
            $idValue = $entity->idValue();
            $idKey = $entity->idKey();

            $hash = $this->hash([$idName]);

            $idsAggregate = $this->data[ActionType::DELETE][$table][$hash]['where'][0][2] ?? [];
            $idsAggregate[] = $idValue;

            $this->data[ActionType::DELETE][$table][$hash] = [
                'where' => [
                    [$idKey, 'IN', $idsAggregate],
                ],
            ];
        }
    }

    /**
     * @param array<int, mixed> $array
     * @return string
     */
    private function hash(array $array): string
    {
        return serialize($array);
    }

    public function delete(PersistAble $entity): void
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
        return empty($this->insert) && empty($this->update && empty($this->delete));
    }

    public function reset(): void
    {
        $this->insert = $this->update = $this->delete = $this->data = [];
    }
}
