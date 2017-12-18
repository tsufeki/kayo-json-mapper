<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\NameMangler\NameMangler;

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

    /**
     * @var NameMangler
     */
    private $nameMangler;

    public function __construct(
        Dumper $dispatchingDumper,
        ClassMetadataProvider $metadataProvider,
        NameMangler $nameMangler
    ) {
        $this->dispatchingDumper = $dispatchingDumper;
        $this->metadataProvider = $metadataProvider;
        $this->nameMangler = $nameMangler;
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
            $mangledName = $this->nameMangler->mangle($property->name);
            $result->{$mangledName} = $this->dispatchingDumper->dump($property->get($value), $context);
        }

        return $result;
    }
}
