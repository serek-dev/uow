<?php


namespace Stwarog\Uow\Fuel;


use Orm\Model;
use Stwarog\Uow\EntityInterface;

class FuelModelAdapter implements EntityInterface
{
    /** @var Model */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function isNew(): bool
    {
        return $this->model->is_new();
    }

    public function isDirty(): bool
    {
        return $this->model->is_changed();
    }
}
