<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader\Instantiator;

interface Instantiator
{
    /**
     * Instantiate new object of given class.
     *
     * @param string    $class
     * @param \stdClass $data  Data which will be loaded into return object, before serialization.
     *
     * @return object
     */
    public function instantiate(string $class, \stdClass $data);
}
