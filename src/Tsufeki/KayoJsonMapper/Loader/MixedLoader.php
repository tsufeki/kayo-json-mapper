<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;

class MixedLoader implements Loader
{
    public function load($data, Type $type, $target = null)
    {
        if (!($type instanceof Types\Mixed_)) {
            throw new UnsupportedTypeException();
        }

        return $data;
    }
}
