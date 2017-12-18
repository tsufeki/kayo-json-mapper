<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader\Instantiator;

class NewInstantiator implements Instantiator
{
    public function instantiate(string $class)
    {
        return new $class();
    }
}
