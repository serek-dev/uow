<?php
declare(strict_types=1);


namespace Stwarog\Uow\IdGenerators;


interface HasIdStrategy
{
    public function idValueGenerationStrategy(): IdGenerationStrategyInterface;
}
