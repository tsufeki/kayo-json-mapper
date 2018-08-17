<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class ScalarLoader implements Loader
{
    const TYPE_MAP = [
        Types\Boolean::class => ['boolean'],
        Types\Float_::class => ['double', 'integer'],
        Types\Integer::class => ['integer'],
        Types\String_::class => ['string'],
        Types\Scalar::class => ['boolean', 'double', 'integer', 'string'],
    ];

    /**
     * @var bool
     */
    private $convertFloatToInt;

    public function __construct(bool $convertFloatToInt = false)
    {
        $this->convertFloatToInt = $convertFloatToInt;
    }

    public function getSupportedTypes(): array
    {
        return ['bool', 'float', 'int', 'string', 'scalar'];
    }

    public function load($data, Type $type, Context $context)
    {
        $expectedTypes = self::TYPE_MAP[get_class($type)] ?? null;

        if ($expectedTypes === null) {
            throw new UnsupportedTypeException();
        }

        if (!in_array(gettype($data), $expectedTypes, true)) {
            if ($this->convertFloatToInt && gettype($data) === 'double' && get_class($type) === Types\Integer::class) {
                return $this->convertFloatToInt($data);
            }

            throw new TypeMismatchException((string)$type, $data, $context);
        }

        return $data;
    }

    private function convertFloatToInt(float $value): int
    {
        if ($value >= PHP_INT_MAX) {
            return PHP_INT_MAX;
        }

        if ($value <= PHP_INT_MIN) {
            return PHP_INT_MIN;
        }

        return (int)$value;
    }
}
