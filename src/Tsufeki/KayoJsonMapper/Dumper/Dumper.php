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
     * @return mixed A dumped representation, ready to be passed to `json_encode()`.
     *
     * @throws UnsupportedTypeException If this dumper doesn't support given value.
     */
    public function dump($value, Context $context);
}
