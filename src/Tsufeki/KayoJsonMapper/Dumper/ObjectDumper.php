<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;

class ObjectDumper implements Dumper
{
    /**
     * @var Dumper
     */
    private $dispatchingDumper;

    /**
     * @var ClassMetadataProvider
     */
    private $metadataProvider;

    public function __construct(Dumper $dispatchingDumper, ClassMetadataProvider $metadataProvider)
    {
        $this->dispatchingDumper = $dispatchingDumper;
        $this->metadataProvider = $metadataProvider;
    }

    public function dump($value, Context $context)
    {
        if (!is_object($value)) {
            throw new UnsupportedTypeException();
        }

        if ($value instanceof \stdClass) {
            $result = new \stdClass();

            foreach (get_object_vars($value) as $name => $propertyValue) {
                $result->$name = $this->dispatchingDumper->dump($propertyValue, $context);
            }

            return $result;
        }

        $class = get_class($value);
        $metadata = $this->metadataProvider->getClassMetadata($class);
        $result = new \stdClass();

        foreach ($metadata->properties as $property) {
            $result->{$property->name} = $this->dispatchingDumper->dump($property->get($value), $context);
        }

        return $result;
    }
}
