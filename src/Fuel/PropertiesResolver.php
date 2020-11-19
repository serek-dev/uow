<?php


namespace Stwarog\Uow\Fuel;


class PropertiesResolver
{
    /** @var array */
    private $props;

    public function __construct(array $fuelFormatProps)
    {
        $this->props = $fuelFormatProps;
        return $this;
    }

    public function toArray(): array
    {
        $parsed = [];

        foreach ($this->props as $key => $value)
        {
            $parsed[] = is_numeric($key) ? $value : $key;
        }

        return $parsed;
    }
}
