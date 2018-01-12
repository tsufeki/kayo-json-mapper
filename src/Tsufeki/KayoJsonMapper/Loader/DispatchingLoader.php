<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class DispatchingLoader implements Loader
{
    /**
     * @var Loader[][]
     */
    private $loaders = [];

    public function add(Loader $loader): self
    {
        foreach ($loader->getSupportedTypes() as $key) {
            $key = strtolower($key);
            if (!isset($this->loaders[$key])) {
                $this->loaders[$key] = [];
            }
            array_unshift($this->loaders[$key], $loader);
        }

        return $this;
    }

    public function getSupportedTypes(): array
    {
        return ['any'];
    }

    public function load($data, Type $type, Context $context)
    {
        $context->push($data);

        try {
            foreach ($this->getLoadersForType($type) as $loader) {
                try {
                    return $loader->load($data, $type, $context);
                } catch (UnsupportedTypeException $e) {
                }
            }
        } finally {
            $context->pop();
        }

        throw new UnsupportedTypeException((string)$type);
    }

    /**
     * @return Loader[]
     */
    private function getLoadersForType(Type $type): array
    {
        $keys = [strtolower((string)$type) => true];

        if ($type instanceof Types\Array_) {
            $keys['array'] = true;
        } elseif ($type instanceof Types\Compound || $type instanceof Types\Nullable) {
            $keys['union'] = true;
        } elseif ($type instanceof Types\Object_) {
            $keys['object'] = true;
        }

        $keys['any'] = true;

        $loaders = [];
        foreach ($keys as $key => $_) {
            foreach ($this->loaders[$key] ?? [] as $loader) {
                $loaders[] = $loader;
            }
        }

        return $loaders;
    }
}
