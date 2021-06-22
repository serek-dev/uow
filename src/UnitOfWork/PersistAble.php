<?php

declare(strict_types=1);

namespace Stwarog\Uow\UnitOfWork;

interface PersistAble
{
    /**
     * @return array<int, mixed> - string instead of mixed irl
     */
    public function columns(): array;

    public function table(): string;

    /**
     * If isNew() then all fields are returned.
     * If isDirty() then only changed values.
     *
     * @return array<int, mixed>
     */
    public function values(): array;

    public function idValue(): ?string;

    public function idKey(): ?string;

    /**
     * @return object
     */
    public function originalClass();

    public function objectHash(): string;
}
