<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class NullLoader implements Loader
{
    /**
     * @var bool
     */
    private $strict;

    public function __construct(bool $strict = true)
    {
        $this->strict = $strict;
    }

    public function load($data, Type $type, Context $context)
    {
        if ($data !== null) {
            throw new UnsupportedTypeException();
        }

        if ($this->strict && !($type instanceof Types\Null_)) {
            throw new TypeMismatchException((string)$type, $data);
        }

        return $data;
    }
}
