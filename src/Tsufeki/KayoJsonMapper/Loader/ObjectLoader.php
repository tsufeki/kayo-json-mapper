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
        bool $throwOnUnknownProperty = true,
        bool $throwOnMissingProperty = true
    ) {
        $this->dispatchingLoader = $dispatchingLoader;
        $this->metadataProvider = $metadataProvider;
        $this->instantiator = $instantiator;
        $this->throwOnUnknownProperty = $throwOnUnknownProperty;
        $this->throwOnMissingProperty = $throwOnMissingProperty;
    }

    public function load($data, Type $type, Context $context)
    {
        if (!($type instanceof Types\Object_)) {
            throw new UnsupportedTypeException();
        }

        if (!is_object($data) || !($data instanceof \stdClass)) {
            throw new TypeMismatchException('stdClass', $data);
        }

        if (in_array((string)$type, ['object', '\\stdClass'], true)) {
            return $data;
        }

        $class = ltrim((string)$type, '\\');
        $target = $this->instantiator->instantiate($class, $data);
        $class = get_class($target);
        $metadata = $this->metadataProvider->getClassMetadata($class);
        $vars = get_object_vars($data);

        foreach ($metadata->properties as $property) {
            if (isset($vars[$property->name])) {
                $value = $this->dispatchingLoader->load($vars[$property->name], $property->type, $context);
                $property->set($target, $value);
                unset($vars[$property->name]);
            } elseif ($this->throwOnMissingProperty) {
                throw new MissingPropertyException($property->name);
            }
        }

        if (!empty($vars) && $this->throwOnUnknownProperty) {
            throw new UnknownPropertyException(array_keys($vars)[0]);
        }

        return $target;
    }
}
