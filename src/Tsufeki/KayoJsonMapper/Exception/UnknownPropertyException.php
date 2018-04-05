<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

class UnknownPropertyException extends InvalidDataException
{
    public function __construct(string $class, string $propertyName)
    {
        parent::__construct("Unrecognized property {$class}::\${$propertyName} found");
    }
}
