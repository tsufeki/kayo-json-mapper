<?php

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;

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

    public function __construct(Loader $dispatchingLoader, ClassMetadataProvider $metadataProvider)
    {
        $this->dispatchingLoader = $dispatchingLoader;
        $this->metadataProvider = $metadataProvider;
    }

    public function load($data, Type $type, $target = null)
    {
        if (!($type instanceof Types\Object_)) {
            throw new UnsupportedTypeException();
        }

        if (!is_object($data) || !($data instanceof \stdClass)) {
            throw new TypeMismatchException();
        }

        if (in_array((string)$type, ['object', '\\stdClass'], true)) {
            return $data;
        }

        $class = ltrim((string)$type, '\\');
        $target = $target ?? new $class();
        $metadata = $this->metadataProvider->getClassMetadata($class);
        $vars = get_object_vars($data);

        foreach ($metadata->properties as $property) {
            if (isset($vars[$property->name])) {
                $value = $this->dispatchingLoader->load($vars[$property->name], $property->type);
                $property->set($target, $value);
                unset($vars[$property->name]);
            }
        }

        return $target;
    }
}
