<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

use Tsufeki\KayoJsonMapper\Context\Context;

class UnknownPropertyException extends InvalidDataException
{
    public function __construct(string $class, string $propertyName, Context $context)
    {
        parent::__construct(
            "Unrecognized property {$class}::\${$propertyName} found"
            . ' at ' . ($context->getPath() ?: '?')
        );
    }
}
