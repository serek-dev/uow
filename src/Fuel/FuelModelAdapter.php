<?php


namespace Stwarog\Uow\Fuel;


use InvalidArgumentException;
use Orm\Model;
use Stwarog\Uow\AutoIncrementIdStrategy;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerationStrategyInterface;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Relations\BelongsTo;
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
        $dataRelations = ReflectionHelper::getValue($this->model, '_data_relations');

        foreach (FuelRelationType::toArray() as $relationTypePropName) {
            $relation = ReflectionHelper::getValue($this->model, $relationTypePropName);

            if (empty($relation)) {
                continue;
            }

            foreach ($relation as $field => $meta) {
                switch ($relationTypePropName) {
                    case FuelRelationType::BELONGS_TO:
                        if (empty($dataRelations[$field])) {
                            break;
                        }
                        $entity = new FuelModelAdapter($dataRelations[$field]);
                        $bag    = new BelongsTo(
                            $entity, $meta['key_from'], $meta['model_to'], $meta['key_to']
                        );
                        $this->relations->add($field, $bag);
                        break;

                    case FuelRelationType::HAS_ONE:

                        break;

                    case FuelRelationType::HAS_MANY:

                        break;

                    default:
                        throw new InvalidArgumentException('Unknown relation type '.$relationTypePropName);
                }
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

    public function idKey(): ?string
    {
        $assoc = array_keys($this->model->get_pk_assoc());

        return reset($assoc);
    }

    public function relations(): RelationBag
    {
        return $this->relations;
    }

    public function setId(string $id): void
    {
        if (empty($this->idKey())) {
            throw new InvalidArgumentException('Attempted to set ID value, but no ID key name specified');
        }
        $this->model[$this->idKey()] = $id;
    }

    public function generateIdValue(DBConnectionInterface $db): void
    {
        $strategy = $this->idValueGenerationStrategy();
        $strategy->handle($this, $db);
    }

    public function idValueGenerationStrategy(): IdGenerationStrategyInterface
    {
        return new AutoIncrementIdStrategy();
    }

    public function originalClass(): object
    {
        return $this->model;
    }

    public function get(string $field)
    {
        return $this->model[$field];
    }

    public function set(string $field, $value)
    {
        $this->model[$field] = $value;
    }
}
