<?php


namespace Stwarog\Uow;


use InvalidArgumentException;
use OutOfBoundsException;

interface EntityInterface
{
    public function isNew(): bool;

    public function table(): string;

    public function isDirty(): bool;

    /**
     * @return array|string[]
     */
    public function columns(): array;

    /**
     * If isNew() then all fields are returned.
     * If isDirty() then only changed values.
     *
     * @return array
     */
    public function values(): array;

    public function idValue(): ?string;

    public function idKey(): string;

    public function idValueGenerationStrategy():  IdGenerationStrategyInterface;

    /**
     * Generate ID by provided strategy and assign it to it self.
     *
     * @param DBConnectionInterface $db
     */
    public function generateIdValue(DBConnectionInterface $db): void;

    public function isIdAutoIncrement(): bool;

    public function relations(): RelationBag;

    public function setId(string $nextAutoIncrementNo): void;

    public function originalClass(): object;

    /**
     * Returns value of given property.
     *
     * @param string $propertyName
     *
     * @return mixed
     * @throws OutOfBoundsException
     */
    public function get(string $propertyName);

    public function set(string $propertyName, $value);

    public function handleBelongsTo(string $field, EntityInterface $related): void;
}
