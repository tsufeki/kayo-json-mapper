<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

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

    private $useRequiredPhpdocTag;

    const REQUIRED_TAG = 'required';
    const OPTIONAL_TAG = 'optional';

    public function __construct(
        CallableMetadataProvider $callableMetadataProvider,
        AccessorStrategy $accessorStrategy,
        PhpdocTypeExtractor $phpdocTypeExtractor,
        bool $guessRequiredProperties = true,
        bool $useRequiredPhpdocTag = true
    ) {
        $this->callableMetadataProvider = $callableMetadataProvider;
        $this->accessorStrategy = $accessorStrategy;
        $this->phpdocTypeExtractor = $phpdocTypeExtractor;
        $this->guessRequiredProperties = $guessRequiredProperties;
        $this->useRequiredPhpdocTag = $useRequiredPhpdocTag;
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        try {
            $metadata = new ClassMetadata();
            $metadata->name = $class;
            $reflectionClass = new \ReflectionClass($class);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                if (!$reflectionProperty->isStatic()) {
                    $propertyMetadata = $this->getPropertyMetadataFromReflection($reflectionProperty);
                    $metadata->properties[] = $propertyMetadata;
                }
            }

            return $metadata;
        } catch (\ReflectionException $e) {
            throw new MetadataException($e->getMessage());
        }
    }

    private function getPropertyMetadataFromReflection(\ReflectionProperty $property): PropertyMetadata
    {
        $metadata = new PropertyMetadata();
        $metadata->name = $property->getName();

        $class = $property->getDeclaringClass();

        $getterReflection = null;
        $getterType = null;
        foreach ($this->accessorStrategy->getGetters($metadata->name) as $getter) {
            if ($getterReflection = $this->getMethod($class, $getter)) {
                $getterMetadata = $this->callableMetadataProvider->getCallableMetadata($getterReflection);
                if (empty($getterMetadata->parameters) || $getterMetadata->parameters[0]->optional) {
                    $metadata->getter = $getter;
                    $getterType = $getterMetadata->returnType;
                    break;
                }
            }
        }

        $tags = $this->phpdocTypeExtractor->getPhpdocTypesByVar($property, 'var');
        $metadata->type = $tags[$metadata->name] ?? $tags[''] ?? $getterType ?? new Mixed_();

        $setterReflection = null;
        foreach ($this->accessorStrategy->getSetters($metadata->name) as $setter) {
            if ($setterReflection = $this->getMethod($class, $setter)) {
                $metadata->setter = $setter;
                break;
            }
        }

        $this->guessRequiredProperty($class, $metadata);
        $this->setRequiredFromPhpdocTags($property, $metadata);
        $this->setRequiredFromPhpdocTags($getterReflection, $metadata);
        $this->setRequiredFromPhpdocTags($setterReflection, $metadata);

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

    private function guessRequiredProperty(\ReflectionClass $class, PropertyMetadata $property)
    {
        if ($this->guessRequiredProperties) {
            $defaults = $class->getDefaultProperties();

            $property->required = ($defaults[$property->name] ?? null) === null
                && !$this->phpdocTypeExtractor->isTypeNullable($property->type);
        }
    }

    private function setRequiredFromPhpdocTags($reflection = null, PropertyMetadata $property)
    {
        if ($this->useRequiredPhpdocTag && $reflection !== null) {
            if ($this->phpdocTypeExtractor->hasPhpdocTag($reflection, self::OPTIONAL_TAG)) {
                $property->required = false;
            }
            if ($this->phpdocTypeExtractor->hasPhpdocTag($reflection, self::REQUIRED_TAG)) {
                $property->required = true;
            }
        }
    }
}
