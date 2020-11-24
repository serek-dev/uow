<?php


namespace Stwarog\Uow;


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
        return spl_object_hash($entity->originalClass());
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
