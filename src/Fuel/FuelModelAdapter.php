<?php declare(strict_types=1);
/*
    Copyright (c) 2020 Sebastian Twaróg <contact@stwarog.com>

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Stwarog\Uow\Fuel;


use InvalidArgumentException;
use Orm\Model;
use Stwarog\Uow\DBConnectionInterface;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\IdGenerators\AutoIncrementIdStrategy;
use Stwarog\Uow\IdGenerators\HasIdStrategy;
use Stwarog\Uow\IdGenerators\IdGenerationStrategyInterface;
use Stwarog\Uow\RelationBag;
use Stwarog\Uow\Relations\BelongsTo;
use Stwarog\Uow\Relations\HasMany;
use Stwarog\Uow\Relations\HasOne;
use Stwarog\Uow\Relations\ManyToMany;

class FuelModelAdapter implements EntityInterface
{
    /** @var Model */
    private $model;
    /** @var RelationBag */
    private $relations;
    private $objectHash;
    private $idKey = '';

    public function __construct(Model $model)
    {
        $this->model     = $model;
        $this->relations = new RelationBag();
        $this->objectHash = spl_object_hash($model);
        $assoc = array_keys($this->model->get_pk_assoc());
        $this->idKey = reset($assoc);
        $this->extractRelations();
    }

    private function extractRelations()
    {
        $dataRelations = _get($this->model, '_data_relations');
        $customData    = _get($this->model, '_custom_data');

        $mergedData = array_merge($dataRelations, $customData);

        foreach (FuelRelationType::toArray() as $relationTypePropName) {
            $relation = _get($this->model, $relationTypePropName);

            if (empty($relation)) {
                continue;
            }

            foreach ($relation as $field => $meta) {

                switch ($relationTypePropName) {

                    case FuelRelationType::BELONGS_TO:
                        $entity = !empty($mergedData[$field]) ? new FuelModelAdapter($mergedData[$field]) : [];
                        $bag    = new BelongsTo($meta['key_from'], $meta['model_to'], $meta['key_to']);
                        $bag->setRelatedData([$entity]);
                        $this->relations->add($field, $bag);
                        break;

                    case FuelRelationType::HAS_ONE:
                        $entity = !empty($mergedData[$field]) ? new FuelModelAdapter($mergedData[$field]) : [];
                        $bag    = new HasOne($meta['key_from'], $meta['model_to'], $meta['key_to']);
                        $bag->setRelatedData([$entity]);
                        $this->relations->add($field, $bag);
                        break;

                    case FuelRelationType::HAS_MANY:
                        $entities = !empty($mergedData[$field]) ? array_map(
                            function (Model $model) {
                                return new FuelModelAdapter($model);
                            },
                            $mergedData[$field]
                        ) : [];

                        $entities = array_values($entities); # normalization, due fuels maps indexes as PK
                        $bag      = new HasMany($meta['key_from'], $meta['model_to'], $meta['key_to']);
                        $bag->setRelatedData($entities);
                        $this->relations->add($field, $bag);
                        break;

                    case FuelRelationType::MANY_TO_MANY:
                        $entities = !empty($mergedData[$field]) ? array_map(
                            function (Model $model) {
                                return new FuelModelAdapter($model);
                            },
                            $mergedData[$field]
                        ) : [];

                        $entities = array_values($entities); # normalization, due fuels maps indexes as PK
                        $bag      = new ManyToMany(
                            $meta['key_from'],
                            $meta['key_through_from'],
                            $meta['table_through'],
                            $meta['key_through_to'],
                            $meta['model_to'],
                            $meta['key_to']
                        );
                        $bag->setRelatedData($entities);
                        $this->relations->add($field, $bag);
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
        $data     = _get($this->model, '_data');
        $original = _get($this->model, '_original');

        return array_diff($data, $original);
    }

    public function table(): string
    {
        return _get($this->model, '_table_name');
    }

    public function columns(): array
    {
        if ($this->isNew()) {
            $data = _get($this->model, '_data');

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
            $data = _get($this->model, '_data');

            return array_values($data);
        }

        return array_values($this->getDifferences());
    }

    public function idValue(): ?string
    {
        return (string) $this->model[$this->idKey()] ?? null;
    }

    public function idKey(): ?string
    {
        return $this->idKey;
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
        if ($this->model instanceof HasIdStrategy) {
            return $this->model->idValueGenerationStrategy();
        }

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

    public function isEmpty(): bool
    {
        return empty($this->toArray());
    }

    public function toArray(): array
    {
        return $this->model->to_array();
    }

    public function objectHash(): string
    {
        return $this->objectHash;
    }
}
