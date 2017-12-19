<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\PropertyAccess;

use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;

class PublicPropertyAccess implements PropertyAccess
{
    public function get($object, PropertyMetadata $property)
    {
        if ($property->getter) {
            return $object->{$property->getter}();
        }

        return $object->{$property->name};
    }

    public function set($object, PropertyMetadata $property, $value)
    {
        if ($property->setter) {
            $object->{$property->setter}($value);
        } else {
            $object->{$property->name} = $value;
        }
    }
}
