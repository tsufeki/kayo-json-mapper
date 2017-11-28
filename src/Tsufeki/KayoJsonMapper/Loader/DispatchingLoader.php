<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;
use Tsufeki\KayoJsonMapper\Context;

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
        foreach ($this->loaders as $loader) {
            try {
                return $loader->load($data, $type, $context->createNested($data));
            } catch (UnsupportedTypeException $e) {
            }
        }

        throw new UnsupportedTypeException();
    }
}
