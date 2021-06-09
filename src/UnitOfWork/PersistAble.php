<?php
declare(strict_types=1);


namespace Stwarog\Uow\UnitOfWork;


interface PersistAble
{
    /**
     * @return array|string[]
     */
    public function columns(): array;

    public function table(): string;

    /**
     * If isNew() then all fields are returned.
     * If isDirty() then only changed values.
     *
     * @return array
     */
    public function values(): array;

    public function idValue(): ?string;

    public function idKey(): ?string;

    public function originalClass();

    public function objectHash(): string;
}
