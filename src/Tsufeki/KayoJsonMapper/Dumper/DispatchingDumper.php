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

    /**
     * @var int|float
     */
    private $maxDepth;

    public function __construct($maxDepth = INF)
    {
        $this->maxDepth = $maxDepth;
    }

    public function add(Dumper $dumper): self
    {
        array_unshift($this->dumpers, $dumper);

        return $this;
    }

    public function dump($value, Context $context)
    {
        if ($context->getDepth() >= $this->maxDepth) {
            return null;
        }

        foreach ($this->dumpers as $dumper) {
            $context->push($value);

            try {
                return $dumper->dump($value, $context);
            } catch (UnsupportedTypeException $e) {
            } finally {
                $context->pop();
            }
        }

        throw new UnsupportedTypeException(null, $value);
    }
}
