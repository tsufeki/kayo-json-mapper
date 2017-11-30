<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

interface Dumper
{
    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws UnsupportedTypeException
     */
    public function dump($value);
}
