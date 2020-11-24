<?php


namespace Stwarog\Uow;


interface EntityInterface extends TouchAble
{
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

    public function idKey(): ?string;

    public function idValueGenerationStrategy(): IdGenerationStrategyInterface;

    /**
     * Generate ID by provided strategy and assign it to it self.
     *
     * @param DBConnectionInterface $db
     */
    public function generateIdValue(DBConnectionInterface $db): void;

    public function relations(): RelationBag;

    public function setId(string $id): void;

    public function originalClass(): object;

    public function get(string $field);

    public function set(string $field, $value);
}
