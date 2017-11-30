<?php

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

class StandardAccessorStrategy implements AccessorStrategy
{
    public function getGetters(string $property): array
    {
        $uppercase = ucfirst($property);

        return [
            'get' . $uppercase,
            'is' . $uppercase,
            $property,
        ];
    }

    public function getSetters(string $property): array
    {
        $uppercase = ucfirst($property);

        return [
            'set' . $uppercase,
            $property,
        ];
    }
}
