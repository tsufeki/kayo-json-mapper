<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

class BadDateTimeFormatException extends InvalidDataException
{
    public function __construct(string $format, array $errors)
    {
        parent::__construct("Bad date format ($format): " . implode('; ', $errors));
    }
}
