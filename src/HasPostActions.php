<?php


namespace Stwarog\Uow;


use Closure;

interface HasPostActions
{
    public function addPostPersist(Closure $closure): void;

    public function getPostPersistClosures(): array;
}
