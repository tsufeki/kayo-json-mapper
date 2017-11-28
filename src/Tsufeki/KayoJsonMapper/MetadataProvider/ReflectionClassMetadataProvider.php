<?php

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Mixed_;
use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;

class ReflectionClassMetadataProvider implements ClassMetadataProvider
{
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
        $type = null;

        if (trim($property->getDocComment())) {
            $docBlock = DocBlockFactory::createInstance()->create($property, $context);

            /** @var Var_ $tag */
            foreach ($docBlock->getTagsByName('var') as $tag) {
                if (in_array($tag->getVariableName(), [$metadata->name, ''], true)) {
                    $type = $tag->getType();
                    break;
                }
            }
        }

        $metadata->type = $type ?? new Mixed_();

        return $metadata;
    }
}
