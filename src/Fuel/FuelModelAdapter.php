<?php


namespace Stwarog\Uow\Fuel;


use InvalidArgumentException;
use Orm\Model;
use Stwarog\Uow\AutoIncrementIdStrategy;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerationStrategyInterface;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Relations\AbstractRelation;
use Stwarog\Uow\Relations\BelongsTo;
use Stwarog\Uow\Utils\ReflectionHelper;

class FuelModelAdapter implements EntityInterface
{
    /** @var Model */
    private $model;
    /** @var RelationBag */
    private $relations;
    /** @var array|AbstractRelation[] */
    private $rel;

    public function __construct(Model $model)
    {
        $this->model     = $model;
        $this->relations = new RelationBag();
        $this->extractRelations();
    }

    private function extractRelations()
    {
        $dataRelations = ReflectionHelper::getValue($this->model, '_data_relations');

        $relations = [] ;

        foreach (FuelRelationType::toArray() as $relationTypePropName) {
            $relation = ReflectionHelper::getValue($this->model, $relationTypePropName);

            if (empty($relation)) {
                continue;
            }

            foreach ($relation as $field => $meta) {
                switch ($relationTypePropName) {
                    case FuelRelationType::BELONGS_TO:
                        $relations[$relationTypePropName][$field] = new BelongsTo(
                            $meta['key_from'], $meta['model_to'], $meta['key_to']
                        );
                        break;

//                default: throw new InvalidArgumentException('Unknown relation type ' . $relationTypePropName);
                }
            }


        }
        $this->rel = $relations;

        foreach ($dataRelations as $relationName => $models) {
            $models = is_array($models) ? $models : [$models];
            foreach ($models as $model) {
                if (empty($model)) {
                    continue;
                }
                $this->relations->add(
                    $this->getRelationType($relationName),
                    new FuelModelAdapter($model)
                );
            }
        }
    }

    public function handleBelongsTo(string $field, EntityInterface $related): void
    {
        if (empty($this->rel[FuelRelationType::BELONGS_TO][$field])) {
            return;
        }
        /** @var AbstractRelation $relation */
        $relation = $this->rel[FuelRelationType::BELONGS_TO][$field];
        $this->set($relation->keyFrom(), $related->get($relation->keyTo()));
    }

    private function getRelationType(string $relationName): FuelRelationType
    {
        foreach (FuelRelationType::toArray() as $type) {
            $schema = ReflectionHelper::getValue($this->model, $type) ?? [];
            $schema = array_keys($schema);
            if (in_array($relationName, $schema)) {
                return new FuelRelationType($type);
            }
        }

        throw new InvalidArgumentException('No relation type found.');
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

    public function originalClassName(): string
    {
        return get_class($this->model);
    }

    public function get(string $propertyName)
    {
        # todo throw exception if not defined
        return $this->model[$propertyName];
    }

    public function set(string $propertyName, $value)
    {
        $this->model[$propertyName] = $value;
    }
}
