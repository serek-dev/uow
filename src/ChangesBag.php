<?php


namespace Stwarog\Uow;


class ChangesBag
{
    private $data = [];

    public function insert(EntityInterface $entity): void
    {
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
        $table   = $entity->table();
        $columns = $entity->columns();
        $values  = $entity->values();
        $id      = $entity->idValue();

        $hash = $this->hash(array_combine($columns, $values));

        $idsAggregate   = $this->data[ActionType::UPDATE][$table][$hash]['where'][0][2] ?? [];
        $idsAggregate[] = $id;

        $this->data[ActionType::UPDATE][$table][$hash] = [
            'where'   => [
                ['id', 'IN', $idsAggregate],
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
        $table   = $entity->table();
        $idName  = $entity->idKey();
        $idValue = $entity->idValue();

        $hash = $this->hash([$idName]);

        $idsAggregate   = $this->data[ActionType::DELETE][$table][$hash]['where'][0][2] ?? [];
        $idsAggregate[] = $idValue;

        $this->data[ActionType::DELETE][$table][$hash] = [
            'where' => [
                ['id', 'IN', $idsAggregate],
            ],
        ];
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }
}
