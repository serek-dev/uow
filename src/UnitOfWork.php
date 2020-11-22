<?php


namespace Stwarog\Uow;


class UnitOfWork
{
    private $data = [];
    private $persistedHashes = [];

    public function insert(EntityInterface $entity): void
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

    private function hash(array $array): string
    {
        return serialize($array);
    }

    public function update(EntityInterface $entity): void
    {
        $this->mark($entity);

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

    public function delete(EntityInterface $entity)
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

    private function mark(EntityInterface $entity): void
    {
        $this->persistedHashes[] = $this->objectHash($entity);
    }

    public function wasPersisted(EntityInterface $entity): bool
    {
        return in_array($this->objectHash($entity), $this->persistedHashes);
    }

    private function objectHash(EntityInterface $entity): string
    {
        return spl_object_hash($entity->originalClass());
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }
}
