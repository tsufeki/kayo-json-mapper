<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context;
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

    public function dump($value, Context $context)
    {
        foreach ($this->dumpers as $dumper) {
            try {
                $context->push($value);

                return $dumper->dump($value, $context);
            } catch (UnsupportedTypeException $e) {
            } finally {
                $context->pop();
            }
        }

        throw new UnsupportedTypeException();
    }
}
