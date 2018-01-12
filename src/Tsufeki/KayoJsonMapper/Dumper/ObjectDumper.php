<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\NameMangler\NameMangler;
use Tsufeki\KayoJsonMapper\PropertyAccess\PropertyAccess;

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

    /**
     * @var PropertyAccess
     */
    private $propertyAccess;

    public function __construct(
        Dumper $dispatchingDumper,
        ClassMetadataProvider $metadataProvider,
        NameMangler $nameMangler,
        PropertyAccess $propertyAccess
    ) {
        $this->dispatchingDumper = $dispatchingDumper;
        $this->metadataProvider = $metadataProvider;
        $this->nameMangler = $nameMangler;
        $this->propertyAccess = $propertyAccess;
    }

    public function getSupportedTypes(): array
    {
        return ['object'];
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
            $propertyValue = $this->propertyAccess->get($value, $property);
            $result->{$mangledName} = $this->dispatchingDumper->dump($propertyValue, $context);
        }

        return $result;
    }
}
