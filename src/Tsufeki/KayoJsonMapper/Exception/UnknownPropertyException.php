<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

class UnknownPropertyException extends InvalidDataException
{
    public function __construct(string $propertyName)
    {
        parent::__construct("Unrecognized property $propertyName found");
    }
}
