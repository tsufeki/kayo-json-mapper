<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader\Instantiator;

class NoConstructorInstantiator implements Instantiator
{
    public function instantiate(string $class)
    {
        return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }
}
