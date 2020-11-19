<?php


namespace Stwarog\Uow\Fuel;


use Orm\Model;
use Stwarog\Uow\AutoIncrementIdStrategy;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerationStrategyInterface;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Utils\ReflectionHelper;

class FuelModelAdapter implements EntityInterface
{
    /** @var Model */
    private $model;
    /** @var RelationBag */
    private $relations;

    public function __construct(Model $model)
    {
        $this->model     = $model;
        $this->relations = new RelationBag();
        $this->extractRelations();
    }

    private function extractRelations()
    {
        $data = ReflectionHelper::getValue($this->model, '_data_relations');

        foreach (FuelRelationType::toArray() as $relationTypePropName) {
            $relation = ReflectionHelper::getValue($this->model, $relationTypePropName);
//            dump($relation);
        }

        foreach ($data as $relationName => $models) {
            $models = is_array($models) ? $models : [$models];
            foreach ($models as $model) {
                if (empty($model)) {
                    continue;
                }
                $this->relations->add(
                    new FuelModelAdapter($model)
                );
            }
        }
    }

    public function isDirty(): bool
    {
        return !empty($this->getDifferences());
    }

    private function getDifferences(): array
    {
        $data     = ReflectionHelper::getValue($this->model, '_data');
        $original = ReflectionHelper::getValue($this->model, '_original');

        return array_diff($data, $original);
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

    public function idValue(): ?string
    {
        return $this->model[$this->idKey()] ?? null;
    }

    public function idKey(): string
    {
        $assoc = array_keys($this->model->get_pk_assoc());

        return reset($assoc);
    }

    public function relations(): RelationBag
    {
        return $this->relations;
    }

    public function isIdAutoIncrement(): bool
    {
        return true;
    }

    public function setId(string $nextAutoIncrementNo): void
    {
        $this->model[$this->idKey()] = $nextAutoIncrementNo;
    }

    public function idValueGenerationStrategy(): IdGenerationStrategyInterface
    {
        if ($this->isIdAutoIncrement()) {
            return new AutoIncrementIdStrategy();
        }
    }

    public function generateIdValue(DBConnectionInterface $db): void
    {
        $strategy = $this->idValueGenerationStrategy();
        $strategy->handle($this, $db);
    }
}
