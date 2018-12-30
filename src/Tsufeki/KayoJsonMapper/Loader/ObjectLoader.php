<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\MissingPropertyException;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnknownPropertyException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\Instantiator;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\NameMangler\NameMangler;
use Tsufeki\KayoJsonMapper\PropertyAccess\PropertyAccess;

class ObjectLoader implements Loader
{
    /**
     * @var Loader
     */
    private $dispatchingLoader;

    /**
     * @var ClassMetadataProvider
     */
    private $metadataProvider;

    /**
     * @var Instantiator
     */
    private $instantiator;

    /**
     * @var NameMangler
     */
    private $nameMangler;

    /**
     * @var PropertyAccess
     */
    private $propertyAccess;

    /**
     * @var bool
     */
    private $throwOnUnknownProperty;

    /**
     * @var bool
     */
    private $throwOnMissingProperty;

    /**
     * @var bool
     */
    private $setToNullOnMissingProperty;

    /**
     * @var bool
     */
    private $acceptArrays;

    public function __construct(
        Loader $dispatchingLoader,
        ClassMetadataProvider $metadataProvider,
        Instantiator $instantiator,
        NameMangler $nameMangler,
        PropertyAccess $propertyAccess,
        bool $throwOnUnknownProperty = true,
        bool $throwOnMissingProperty = true,
        bool $setToNullOnMissingProperty = false,
        bool $acceptArrays = false
    ) {
        $this->dispatchingLoader = $dispatchingLoader;
        $this->metadataProvider = $metadataProvider;
        $this->instantiator = $instantiator;
        $this->nameMangler = $nameMangler;
        $this->propertyAccess = $propertyAccess;
        $this->throwOnUnknownProperty = $throwOnUnknownProperty;
        $this->throwOnMissingProperty = $throwOnMissingProperty;
        $this->setToNullOnMissingProperty = $setToNullOnMissingProperty;
        $this->acceptArrays = $acceptArrays;
    }

    public function getSupportedTypes(): array
    {
        return ['object'];
    }

    public function load($data, Type $type, Context $context)
    {
        if (!($type instanceof Types\Object_)) {
            throw new UnsupportedTypeException();
        }

        // If its an array, it's probably useful if it contains some string keys
        if ($this->acceptArrays && is_array($data) && !empty($data) && !array_product(array_map('is_numeric', array_keys($data)))) {
            $data = (object)$data;
        }

        if (!is_object($data) || !($data instanceof \stdClass)) {
            throw new TypeMismatchException('stdClass|array', $data, $context);
        }

        if (in_array((string)$type, ['object', '\\stdClass'], true)) {
            $result = new \stdClass();

            foreach (get_object_vars($data) as $name => $value) {
                $result->$name = $this->dispatchingLoader->load($value, new Types\Mixed_(), $context);
            }

            return $result;
        }

        $class = ltrim((string)$type, '\\');
        $target = $this->instantiator->instantiate($class);
        $class = get_class($target);
        $metadata = $this->metadataProvider->getClassMetadata($class);
        $vars = get_object_vars($data);

        foreach ($metadata->properties as $property) {
            $mangledName = $this->nameMangler->mangle($property->name);
            if (array_key_exists($mangledName, $vars)) {
                $context->pushPath("->$property->name");
                try {
                    $value = $this->dispatchingLoader->load($vars[$mangledName], $property->type, $context);
                    $this->propertyAccess->set($target, $property, $value);
                } finally {
                    $context->popPath();
                    unset($vars[$mangledName]);
                }
            } else {
                if ($property->required && $this->throwOnMissingProperty) {
                    throw new MissingPropertyException($class, $property->name, $context);
                }
                if ($this->setToNullOnMissingProperty) {
                    $this->propertyAccess->set($target, $property, null);
                }
            }
        }

        if (!empty($vars) && $this->throwOnUnknownProperty) {
            throw new UnknownPropertyException($class, (string)array_keys($vars)[0], $context);
        }

        return $target;
    }
}
