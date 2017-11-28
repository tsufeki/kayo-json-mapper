<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Context;

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

    public function dump($value, Context $context)
    {
        foreach ($this->dumpers as $dumper) {
            try {
                return $dumper->dump($value, $context->createNested($value));
            } catch (UnsupportedTypeException $e) {
            }
        }

        throw new UnsupportedTypeException();
    }
}
