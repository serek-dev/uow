<?php


namespace Stwarog\Uow;


interface TouchAble
{
    public function toArray(): array;

    public function isEmpty(): bool;

    /**
     * Should determine if given object is modified.
     * Attention! EntityInterface should not rely on it's relations!
     * For example relation can be new or dirty, but it doesn't mean
     * that the parent object is modified.
     *
     * If object is new then it's dirty as well.
     *
     * @return bool
     */
    public function isDirty(): bool;

    public function isNew(): bool;
}
