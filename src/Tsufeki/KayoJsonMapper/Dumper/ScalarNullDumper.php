<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class ScalarNullDumper implements Dumper
{
    public function getSupportedTypes(): array
    {
        return ['bool', 'float', 'int', 'null', 'string'];
    }

    public function dump($value, Context $context)
    {
        if ($value !== null && !is_scalar($value)) {
            throw new UnsupportedTypeException();
        }

        return $value;
    }
}
