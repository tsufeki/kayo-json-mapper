<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\PropertyAccess;

use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;

class PrivatePropertyAccess implements PropertyAccess
{
    private function getReflection($object, PropertyMetadata $property): \ReflectionProperty
    {
        $reflection = new \ReflectionProperty($object, $property->name);
        $reflection->setAccessible(true);

        return $reflection;
    }

    public function get($object, PropertyMetadata $property)
    {
        if ($property->getter) {
            return $object->{$property->getter}();
        }

        return $this->getReflection($object, $property)->getValue($object);
    }

    public function set($object, PropertyMetadata $property, $value)
    {
        if ($property->setter) {
            $object->{$property->setter}($value);
        } else {
            $this->getReflection($object, $property)->setValue($object, $value);
        }
    }
}
