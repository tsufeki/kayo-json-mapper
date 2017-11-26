<?php

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class ArrayDumper implements Dumper
{
    /**
     * @var Dumper
     */
    private $dispatchingDumper;

    public function __construct(Dumper $dispatchingDumper)
    {
        $this->dispatchingDumper = $dispatchingDumper;
    }

    public function dump($value)
    {
        if (!is_array($value)) {
            throw new UnsupportedTypeException();
        }

        $result = [];

        foreach ($value as $element) {
            $result[] = $this->dispatchingDumper->dump($element);
        }

        return $result;
    }
}
