<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

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

    public function load($data, Type $type, Context $context)
    {
        if (!($type instanceof Types\Array_)) {
            throw new UnsupportedTypeException();
        }

        if (is_object($data) && $data instanceof \stdClass) {
            $data = get_object_vars($data);
        }

        if (!is_array($data)) {
            throw new TypeMismatchException('array|stdClass', $data);
        }

        $result = [];
        $elementType = $type->getValueType();
        foreach ($data as $key => $element) {
            $result[$key] = $this->dispatchingLoader->load($element, $elementType, $context);
        }

        return $result;
    }
}
