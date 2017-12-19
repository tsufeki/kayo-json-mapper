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

    public function __construct(
        Loader $dispatchingLoader,
        ClassMetadataProvider $metadataProvider,
        Instantiator $instantiator,
        NameMangler $nameMangler,
        PropertyAccess $propertyAccess,
        bool $throwOnUnknownProperty = true,
        bool $throwOnMissingProperty = true
    ) {
        $this->dispatchingLoader = $dispatchingLoader;
        $this->metadataProvider = $metadataProvider;
        $this->instantiator = $instantiator;
        $this->nameMangler = $nameMangler;
        $this->propertyAccess = $propertyAccess;
        $this->throwOnUnknownProperty = $throwOnUnknownProperty;
        $this->throwOnMissingProperty = $throwOnMissingProperty;
    }

    public function load($data, Type $type, Context $context)
    {
        if (!($type instanceof Types\Object_)) {
            throw new UnsupportedTypeException();
        }

        if (is_array($data)) {
            $data = (object)$data;
        }

        if (!is_object($data) || !($data instanceof \stdClass)) {
            throw new TypeMismatchException('stdClass|array', $data);
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
            if (isset($vars[$mangledName])) {
                $value = $this->dispatchingLoader->load($vars[$mangledName], $property->type, $context);
                $this->propertyAccess->set($target, $property, $value);
                unset($vars[$mangledName]);
            } elseif ($property->required && $this->throwOnMissingProperty) {
                throw new MissingPropertyException($property->name);
            }
        }

        if (!empty($vars) && $this->throwOnUnknownProperty) {
            throw new UnknownPropertyException(array_keys($vars)[0]);
        }

        return $target;
    }
}
