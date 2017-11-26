<?php

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class DispatchingDumper implements Dumper
{
    /**
     * @var Dumper[]
     */
    private $dumpers = [];

    public function add(Dumper $dumper): self
    {
        array_unshift($this->dumpers, $dumper);

        return $this;
    }

    public function dump($value)
    {
        foreach ($this->dumpers as $dumper) {
            try {
                return $dumper->dump($value);
            } catch (UnsupportedTypeException $e) {
            }
        }

        throw new UnsupportedTypeException();
    }
}
