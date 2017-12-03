<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;

class ScalarLoader implements Loader
{
    const TYPE_MAP = [
        Types\Boolean::class => ['boolean'],
        Types\Float_::class => ['double', 'integer'],
        Types\Integer::class => ['integer'],
        Types\Null_::class => ['NULL'],
        Types\String_::class => ['string'],
        Types\Scalar::class => ['boolean', 'double', 'integer', 'NULL', 'string'],
    ];

    public function load($data, Type $type, Context $context)
    {
        $expectedTypes = self::TYPE_MAP[get_class($type)] ?? null;

        if ($expectedTypes === null) {
            throw new UnsupportedTypeException();
        }

        if (!in_array(gettype($data), $expectedTypes, true)) {
            throw new TypeMismatchException((string)$type, $data);
        }

        return $data;
    }
}
