<?php

namespace Stwarog\Uow;

use Closure;

interface HasPostActions
{
    public function addPostPersist(Closure $closure): void;

    /**
     * @return array<Closure>
     */
    public function getPostPersistClosures(): array;
}
