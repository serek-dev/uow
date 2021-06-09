<?php
declare(strict_types=1);


namespace Stwarog\Uow;


interface TouchAble
{
    /**
     * Returns stored data. For Entity it should return array_combine of columns & values.
     * @return array
     */
    public function toArray(): array;

    public function isEmpty(): bool;

    /**
     * Should determine if given object is modified.
     * Attention! EntityInterface should not rely on it's relations!
     * For example relation can be new or dirty, but it doesn't mean
     * that the parent object is modified.
     *
     * If object is new then it's dirty as well, but when is dirty != new.
     *
     * @return bool
     */
    public function isDirty(): bool;

    public function isNew(): bool;
}
