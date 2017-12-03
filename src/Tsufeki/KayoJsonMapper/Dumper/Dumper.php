<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

interface Dumper
{
    /**
     * @param mixed   $value
     * @param Context $context
     *
     * @return mixed
     *
     * @throws UnsupportedTypeException
     */
    public function dump($value, Context $context);
}
