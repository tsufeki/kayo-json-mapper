<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use Tsufeki\KayoJsonMapper\Context\Context;

class ReplacingLoader implements Loader
{
    /**
     * @var array<string,Type|callable>
     */
    private $typeMap = [];

    /**
     * @var Loader
     */
    private $dispatchingLoader;

    /**
     * @var TypeResolver
     */
    private $resolver;

    public function __construct(Loader $dispatchingLoader)
    {
        $this->dispatchingLoader = $dispatchingLoader;
        $this->resolver = new TypeResolver();
    }

    public function replaceType(string $replacedType, string $replacingType): self
    {
        $this->typeMap[strtolower($replacedType)] = $this->resolver->resolve($replacingType);

        return $this;
    }

    /**
     * @param callable $replacingTypeCallback ($data, string $type) -> string $replacingType
     */
    public function replaceTypeCallback(string $replacedType, callable $replacingTypeCallback): self
    {
        $this->typeMap[strtolower($replacedType)] = $replacingTypeCallback;

        return $this;
    }

    public function load($data, Type $type, Context $context)
    {
        $typeKey = strtolower((string)$type);
        $type = $this->typeMap[$typeKey] ?? $type;

        if (is_callable($type)) {
            $type = $this->resolver->resolve($type($data, $typeKey));
        }

        // Pop the context to avoid InfiniteRecursionException as we are dispatching
        // the same object for a second time.
        $context->pop();

        return $this->dispatchingLoader->load($data, $type, $context);
    }
}
