<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader\Instantiator;

interface Instantiator
{
    /**
     * @param string    $class
     * @param \stdClass $data
     *
     * @return object
     */
    public function instantiate(string $class, \stdClass $data);
}
