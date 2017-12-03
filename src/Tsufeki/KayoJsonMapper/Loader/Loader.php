<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

interface Loader
{
    /**
     * @param mixed   $data
     * @param Type    $type
     * @param Context $context
     *
     * @return mixed
     *
     * @throws UnsupportedTypeException
     * @throws TypeMismatchException
     */
    public function load($data, Type $type, Context $context);
}
