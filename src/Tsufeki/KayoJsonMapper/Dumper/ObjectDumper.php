<?php

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\MetadataProvider;

class ObjectDumper implements Dumper
{
    /**
     * @var Dumper
     */
    private $dispatchingDumper;

    /**
     * @var MetadataProvider
     */
    private $metadataProvider;

    public function __construct(Dumper $dispatchingDumper, MetadataProvider $metadataProvider)
    {
        $this->dispatchingDumper = $dispatchingDumper;
        $this->metadataProvider = $metadataProvider;
    }

    public function dump($value)
    {
        if (!is_object($value)) {
            throw new UnsupportedTypeException();
        }

        if ($value instanceof \stdClass) {
            return $value;
        }

        $class = get_class($value);
        $metadata = $this->metadataProvider->getClassMetadata($class);
        $result = new \stdClass();

        foreach ($metadata->properties as $property) {
            $result->{$property->name} = $this->dispatchingDumper->dump($property->get($value));
        }

        return $result;
    }
}
