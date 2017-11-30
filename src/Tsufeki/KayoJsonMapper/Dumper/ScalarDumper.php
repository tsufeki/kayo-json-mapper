<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class ScalarDumper implements Dumper
{
    public function dump($value)
    {
        if ($value !== null && !is_scalar($value)) {
            throw new UnsupportedTypeException();
        }

        return $value;
    }
}
