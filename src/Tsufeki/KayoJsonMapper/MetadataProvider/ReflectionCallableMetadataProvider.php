<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Mixed_;
use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\CallableMetadata;
use Tsufeki\KayoJsonMapper\Metadata\ParameterMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc\PhpdocTypeExtractor;

class ReflectionCallableMetadataProvider implements CallableMetadataProvider
{
    /**
     * @var PhpdocTypeExtractor
     */
    private $phpdocTypeExtractor;

    public function __construct(PhpdocTypeExtractor $phpdocTypeExtractor)
    {
        $this->phpdocTypeExtractor = $phpdocTypeExtractor;
    }

    public function getCallableMetadata($callable): CallableMetadata
    {
        if ($callable instanceof \ReflectionFunctionAbstract) {
            $reflectionCallable = $callable;
        } elseif (is_callable($callable)) {
            try {
                $reflectionCallable = $this->getCallableReflection($callable);
                // @codeCoverageIgnoreStart
            } catch (\ReflectionException $e) {
                // This should never happen, as we already
                // check the callable above
                throw new MetadataException($e->getMessage());
            }
            // @codeCoverageIgnoreEnd
        } else {
            throw new MetadataException();
        }

        $metadata = new CallableMetadata();
        $phpdocTypes = $this->phpdocTypeExtractor->getPhpdocTypesByVar($reflectionCallable, 'param');

        foreach ($reflectionCallable->getParameters() as $reflectionParameter) {
            $metadata->parameters[] = $this->getParameterMetadata(
                $reflectionParameter,
                $phpdocTypes[$reflectionParameter->getName()] ?? null
            );
        }

        $typeResolver = new TypeResolver();
        $returnType = $this->resolveReflectionType($reflectionCallable->getReturnType());
        $phpdocReturnType = $this->phpdocTypeExtractor->getPhpdocType($reflectionCallable, 'return');
        $metadata->returnType = $returnType ?? $phpdocReturnType ?? new Mixed_();

        return $metadata;
    }

    private function getCallableReflection($callable): \ReflectionFunctionAbstract
    {
        if (is_string($callable)) {
            if (strpos($callable, '::') !== false) {
                return new \ReflectionMethod($callable);
            }

            return new \ReflectionFunction($callable);
        }

        if (is_array($callable)) {
            return new \ReflectionMethod(...$callable);
        }

        if ($callable instanceof \Closure) {
            return new \ReflectionFunction($callable);
        }

        return new \ReflectionMethod($callable, '__invoke');
    }

    private function getParameterMetadata(\ReflectionParameter $parameter, Type $phpdocType = null): ParameterMetadata
    {
        $metadata = new ParameterMetadata();
        $metadata->name = $parameter->getName();
        $metadata->optional = $parameter->isOptional();
        $metadata->variadic = $parameter->isVariadic();

        $typeResolver = new TypeResolver();
        $type = $this->resolveReflectionType($parameter->getType());
        $metadata->type = $type ?? $phpdocType ?? new Mixed_();

        if ($metadata->optional && !$metadata->variadic) {
            $metadata->defaultValue = $parameter->getDefaultValue();
        }

        return $metadata;
    }

    /**
     * @return Type|null
     */
    private function resolveReflectionType(\ReflectionType $reflectionType = null)
    {
        if ($reflectionType === null) {
            return null;
        }

        $typeResolver = new TypeResolver();
        $nullable = $reflectionType->allowsNull() ? '|null' : '';
        $type = $typeResolver->resolve($reflectionType->getName() . $nullable);

        return $type;
    }
}
