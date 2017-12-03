<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

class UnsupportedTypeException extends MapperException
{
    public function __construct(string $type = null, $value = null)
    {
        if ($type === null && $value !== null) {
            $type = strtolower(gettype($value));
            if ($type === 'double') {
                $type = 'float';
            } elseif ($type === 'object') {
                $type = get_class($value);
            }
        }

        if ($type) {
            $type = ' ' . $type;
        }

        parent::__construct("Type$type is not supported");
    }
}
