<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\PropertyAccess;

use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;

interface PropertyAccess
{
    /**
     * @return mixed
     */
    public function get($object, PropertyMetadata $property);

    public function set($object, PropertyMetadata $property, $value);
}
