<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

use Tsufeki\KayoJsonMapper\Context\Context;

class BadDateTimeFormatException extends InvalidDataException
{
    public function __construct(string $format, array $errors, string $data, Context $context)
    {
        parent::__construct(
            "Bad date format: expected '$format', got '$data': "
            . implode('; ', $errors)
            . ' at ' . ($context->getPath() ?: '?')
        );
    }
}
