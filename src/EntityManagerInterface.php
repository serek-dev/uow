<?php

namespace Stwarog\Uow;

interface EntityManagerInterface
{
    public function persist(EntityInterface $entity): void;

    public function delete(EntityInterface $entity): void;

    public function flush(): void;
}
