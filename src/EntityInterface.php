<?php


namespace Stwarog\Uow;


interface EntityInterface
{
    public function isNew(): bool;

    public function isDirty(): bool;
}
