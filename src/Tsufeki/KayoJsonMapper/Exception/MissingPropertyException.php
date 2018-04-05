<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

class MissingPropertyException extends InvalidDataException
{
    public function __construct(string $class, string $propertyName)
    {
        parent::__construct("Required property {$class}::\${$propertyName} missing");
    }
}
