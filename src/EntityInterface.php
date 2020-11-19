<?php


namespace Stwarog\Uow;


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

    public function idValue(): string;

    public function idKey(): string;
}
