<?php


namespace Stwarog\Uow;


interface TouchAble
{
    public function toArray(): array;

    public function isEmpty(): bool;

    public function isDirty(): bool;

    public function isNew(): bool;
}
