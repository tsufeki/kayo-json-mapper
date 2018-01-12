<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class MixedLoader implements Loader
{
    public function getSupportedTypes(): array
    {
        return ['mixed'];
    }

    public function load($data, Type $type, Context $context)
    {
        if (!($type instanceof Types\Mixed_)) {
            throw new UnsupportedTypeException();
        }

        return $data;
    }
}
