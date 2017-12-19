<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Mixed_;
use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\AccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc\PhpdocTypeExtractor;

class ReflectionClassMetadataProvider implements ClassMetadataProvider
{
    /**
     * @var CallableMetadataProvider
     */
    private $callableMetadataProvider;

    /**
     * @var AccessorStrategy
     */
    private $accessorStrategy;

    /**
     * @var PhpdocTypeExtractor
     */
    private $phpdocTypeExtractor;

    /**
     * @var bool
     */
    private $guessRequiredProperties;

    public function __construct(
        CallableMetadataProvider $callableMetadataProvider,
        AccessorStrategy $accessorStrategy,
        PhpdocTypeExtractor $phpdocTypeExtractor,
        bool $guessRequiredProperties = true
    ) {
        $this->callableMetadataProvider = $callableMetadataProvider;
        $this->accessorStrategy = $accessorStrategy;
        $this->phpdocTypeExtractor = $phpdocTypeExtractor;
        $this->guessRequiredProperties = $guessRequiredProperties;
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        try {
            $metadata = new ClassMetadata();
            $metadata->name = $class;
            $reflectionClass = new \ReflectionClass($class);
            $context = (new ContextFactory())->createFromReflector($reflectionClass);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                if (!$reflectionProperty->isStatic()) {
                    $propertyMetadata = $this->getPropertyMetadataFromReflection($reflectionProperty, $context);
                    $metadata->properties[] = $propertyMetadata;
                }
            }

            if ($this->guessRequiredProperties) {
                $this->markRequiredProperties($reflectionClass, $metadata->properties);
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

        $class = $property->getDeclaringClass();

        $getterType = null;
        foreach ($this->accessorStrategy->getGetters($metadata->name) as $getter) {
            if ($method = $this->getMethod($class, $getter)) {
                $getterMetadata = $this->callableMetadataProvider->getCallableMetadata($method);
                if (empty($getterMetadata->parameters) || $getterMetadata->parameters[0]->optional) {
                    $metadata->getter = $getter;
                    $getterType = $getterMetadata->returnType;
                    break;
                }
            }
        }

        $tags = $this->phpdocTypeExtractor->getPhpdocTypesByVar($property, 'var');
        $metadata->type = $tags[$metadata->name] ?? $tags[''] ?? $getterType ?? new Mixed_();

        foreach ($this->accessorStrategy->getSetters($metadata->name) as $setter) {
            if ($method = $this->getMethod($class, $setter)) {
                $metadata->setter = $setter;
                break;
            }
        }

        return $metadata;
    }

    /**
     * @return \ReflectionMethod|null
     */
    private function getMethod(\ReflectionClass $class, string $method)
    {
        if ($class->hasMethod($method)) {
            $reflectionMethod = $class->getMethod($method);
            if ($reflectionMethod->isPublic()) {
                return $reflectionMethod;
            }
        }

        return null;
    }

    /**
     * @param PropertyMetadata[] $properties
     */
    private function markRequiredProperties(\ReflectionClass $class, array $properties)
    {
        $defaults = $class->getDefaultProperties();

        foreach ($properties as $property) {
            $property->required = ($defaults[$property->name] ?? null) === null
                && !$this->phpdocTypeExtractor->isTypeNullable($property->type);
        }
    }
}
