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
        $data     = ReflectionHelper::getValue($this->model, '_data');
        $original = ReflectionHelper::getValue($this->model, '_original');

        # todo check relation, original method from ORM is not working due my customizations
        return $data !== $original;
    }

    public function table(): string
    {
        return ReflectionHelper::getValue($this->model, '_table_name');
    }

    public function columns(): array
    {
        if ($this->isNew()) {
            return ReflectionHelper::getValue($this->model, '_properties');
        }
        [$old, $new] = $this->model->get_diff();

        return array_keys($new);
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
        [$old, $new] = $this->model->get_diff();

        return array_values($new);

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
