<?php


namespace Stwarog\Uow;


interface HasIdStrategy
{
    public function idValueGenerationStrategy(): IdGenerationStrategyInterface;
}
