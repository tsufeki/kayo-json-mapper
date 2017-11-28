<?php

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Mixed_;
use Tsufeki\KayoJsonMapper\CallableMetadataProvider;
use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;

class ReflectionClassMetadataProvider implements ClassMetadataProvider
{
    /**
     * @var PhpdocTypeExtractor
     */
    private $phpdocTypeExtractor;

    public function __construct(
        PhpdocTypeExtractor $phpdocTypeExtractor = null
    ) {
        $this->phpdocTypeExtractor = $phpdocTypeExtractor ?? new PhpdocTypeExtractor();
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        try {
            $metadata = new ClassMetadata();
            $metadata->name = $class;
            $reflectionClass = new \ReflectionClass($class);
            $context = (new ContextFactory())->createFromReflector($reflectionClass);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $propertyMetadata = $this->getPropertyMetadataFromReflection($reflectionProperty, $context);
                $metadata->properties[] = $propertyMetadata;
            }

            return $metadata;
        } catch (\ReflectionException $e) {
            throw new MetadataException($e->getMessage());
        }
    }

    private function getPropertyMetadataFromReflection(\ReflectionProperty $property, Context $context): PropertyMetadata
    {
        $metadata = new PropertyMetadata();
        $metadata->name = $property->getName();
        $tags = $this->phpdocTypeExtractor->getPhpdocTypesByVar($property, 'var');

        $metadata->type = $tags[$metadata->name] ?? $tags[''] ?? new Mixed_();

        return $metadata;
    }
}
