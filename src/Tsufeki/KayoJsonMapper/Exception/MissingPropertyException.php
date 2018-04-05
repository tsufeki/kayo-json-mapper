<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

use Tsufeki\KayoJsonMapper\Context\Context;

class MissingPropertyException extends InvalidDataException
{
    public function __construct(string $class, string $propertyName, Context $context)
    {
        parent::__construct(
            "Required property {$class}::\${$propertyName} missing"
            . ' at ' . ($context->getPath() ?: '?')
        );
    }
}
