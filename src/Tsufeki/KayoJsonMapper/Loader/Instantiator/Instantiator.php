<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader\Instantiator;

interface Instantiator
{
    /**
     * Instantiate new object of given class.
     *
     * @param string $class
     *
     * @return object
     */
    public function instantiate(string $class);
}
