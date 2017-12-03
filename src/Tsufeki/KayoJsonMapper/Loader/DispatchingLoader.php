<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class DispatchingLoader implements Loader
{
    /**
     * @var Loader[]
     */
    private $loaders = [];

    public function add(Loader $loader): self
    {
        array_unshift($this->loaders, $loader);

        return $this;
    }

    public function load($data, Type $type, Context $context)
    {
        $context->push($data);

        try {
            foreach ($this->loaders as $loader) {
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
}
