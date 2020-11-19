<?php


namespace Stwarog\Uow\Fuel;


use Orm\Model;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\Utils\ReflectionHelper;

class FuelModelAdapter implements EntityInterface
{
    /** @var Model */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function isDirty(): bool
    {
        return !empty($this->getDifferences());
    }

    public function table(): string
    {
        return ReflectionHelper::getValue($this->model, '_table_name');
    }

    public function columns(): array
    {
        if ($this->isNew()) {
            $data = ReflectionHelper::getValue($this->model, '_data');

            return array_keys($data);
        }

        return array_keys($this->getDifferences());
    }

    public function isNew(): bool
    {
        return $this->model->is_new();
    }

    public function values(): array
    {
        if ($this->isNew()) {
            $data = ReflectionHelper::getValue($this->model, '_data');

            return array_values($data);
        }

        return array_values($this->getDifferences());
    }

    private function getDifferences(): array
    {
        $data     = ReflectionHelper::getValue($this->model, '_data');
        $original = ReflectionHelper::getValue($this->model, '_original');

        return array_diff($data, $original);
    }

    public function idValue(): string
    {
        return $this->model[$this->idKey()];
    }

    public function idKey(): string
    {
        $assoc = array_keys($this->model->get_pk_assoc());

        return reset($assoc);
    }
}
