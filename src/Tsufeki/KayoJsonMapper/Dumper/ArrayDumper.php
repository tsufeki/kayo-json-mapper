<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
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

    public function getSupportedTypes(): array
    {
        return ['array'];
    }

    public function dump($value, Context $context)
    {
        if (!is_array($value)) {
            throw new UnsupportedTypeException();
        }

        $result = [];

        foreach ($value as $key => $element) {
            $result[$key] = $this->dispatchingDumper->dump($element, $context);
        }

        return $result;
    }
}
