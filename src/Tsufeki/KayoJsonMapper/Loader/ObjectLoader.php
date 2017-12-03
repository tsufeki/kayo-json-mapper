<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Instantiator;
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

    /**
     * @var Instantiator
     */
    private $instantiator;

    public function __construct(
        Loader $dispatchingLoader,
        ClassMetadataProvider $metadataProvider,
        Instantiator $instantiator
    ) {
        $this->dispatchingLoader = $dispatchingLoader;
        $this->metadataProvider = $metadataProvider;
        $this->instantiator = $instantiator;
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
        $target = $context->getTargetObject() ?? $this->instantiator->instantiate($class, $data);
        $metadata = $this->metadataProvider->getClassMetadata($class);
        $vars = get_object_vars($data);

        foreach ($metadata->properties as $property) {
            if (isset($vars[$property->name])) {
                $value = $this->dispatchingLoader->load($vars[$property->name], $property->type, $context);
                $property->set($target, $value);
                unset($vars[$property->name]);
            }
        }

        return $target;
    }
}
