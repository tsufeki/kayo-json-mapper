<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

interface Loader
{
    /**
     * @param mixed   $data    As returned by `json_decode()` i.e only stdClass,
     *                         arrays and scalars.
     * @param Type    $type    Expected type.
     * @param Context $context
     *
     * @return mixed Unserialized value.
     *
     * @throws UnsupportedTypeException
     * @throws TypeMismatchException
     */
    public function load($data, Type $type, Context $context);
}
