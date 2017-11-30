<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use phpDocumentor\Reflection\Type;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

interface Loader
{
    /**
     * @param mixed      $data
     * @param Type       $type
     * @param mixed|null $target
     *
     * @return mixed
     *
     * @throws UnsupportedTypeException
     * @throws TypeMismatchException
     */
    public function load($data, Type $type, $target = null);
}
