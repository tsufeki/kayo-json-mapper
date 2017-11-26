<?php

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;

class ArrayLoader implements Loader
{
    /**
     * @var Loader
     */
    private $dispatchingLoader;

    public function __construct(Loader $dispatchingLoader)
    {
        $this->dispatchingLoader = $dispatchingLoader;
    }

    public function load($data, Type $type, $target = null)
    {
        if (!($type instanceof Types\Array_)) {
            throw new UnsupportedTypeException();
        }

        if (!is_array($data)) {
            throw new TypeMismatchException();
        }

        $result = [];
        $elementType = $type->getValueType();
        foreach ($data as $element) {
            $result[] = $this->dispatchingLoader->load($element, $elementType);
        }

        return $result;
    }
}
