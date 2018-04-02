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

    /**
     * @var bool
     */
    private $acceptStdClass;

    public function __construct(Loader $dispatchingLoader, bool $acceptStdClass = false)
    {
        $this->dispatchingLoader = $dispatchingLoader;
        $this->acceptStdClass = $acceptStdClass;
    }

    public function getSupportedTypes(): array
    {
        return ['array'];
    }

    public function load($data, Type $type, Context $context)
    {
        if (!($type instanceof Types\Array_)) {
            throw new UnsupportedTypeException();
        }

        if ($this->acceptStdClass && $data instanceof \stdClass) {
            $data = get_object_vars($data);
        }

        if (!is_array($data)) {
            throw new TypeMismatchException('array' . ($this->acceptStdClass ? '|stdClass' : ''), $data, $context);
        }

        $result = [];
        $elementType = $type->getValueType();
        foreach ($data as $key => $element) {
            $context->pushPath("[$key]");
            try {
                $result[$key] = $this->dispatchingLoader->load($element, $elementType, $context);
            } finally {
                $context->popPath();
            }
        }

        return $result;
    }
}
