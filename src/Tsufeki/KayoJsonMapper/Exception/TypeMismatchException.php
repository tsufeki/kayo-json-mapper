<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

use Tsufeki\KayoJsonMapper\Context\Context;

class TypeMismatchException extends InvalidDataException
{
    public function __construct(string $expectedType, $actualData, Context $context)
    {
        $actualType = strtolower(gettype($actualData));
        if ($actualType === 'double') {
            $actualType = 'float';
        } elseif ($actualType === 'object') {
            $actualType = get_class($actualData);
        }

        $actualDataString = '';
        if (is_scalar($actualData) && !is_string($actualData) && $actualData !== null) {
            $actualDataString = ' (' . var_export($actualData, true) . ')';
        }

        parent::__construct(
            "Expected value of type $expectedType, got $actualType$actualDataString"
            . ' at ' . ($context->getPath() ?: '?')
        );
    }
}
