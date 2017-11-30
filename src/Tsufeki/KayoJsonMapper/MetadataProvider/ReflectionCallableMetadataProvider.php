<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Mixed_;
use Tsufeki\KayoJsonMapper\CallableMetadataProvider;
use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\CallableMetadata;
use Tsufeki\KayoJsonMapper\Metadata\ParameterMetadata;

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

    public function getCallableMetadata(callable $callable): CallableMetadata
    {
        $metadata = new CallableMetadata();

        try {
            $reflectionCallable = $this->getCallableReflection($callable);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            // This should never happen, as we already
            // check the callable through the typehint
            throw new MetadataException($e->getMessage());
        }
        // @codeCoverageIgnoreEnd

        $phpdocTypes = $this->phpdocTypeExtractor->getPhpdocTypesByVar($reflectionCallable, 'param');

        foreach ($reflectionCallable->getParameters() as $reflectionParameter) {
            $metadata->parameters[] = $this->getParameterMetadata(
                $reflectionParameter,
                $phpdocTypes[$reflectionParameter->getName()] ?? null
            );
        }

        $typeResolver = new TypeResolver();
        $returnType = null;
        if ($reflectionCallable->hasReturnType()) {
            $returnType = $typeResolver->resolve((string)$reflectionCallable->getReturnType());
        }

        $phpdocReturnType = $this->phpdocTypeExtractor->getPhpdocType($reflectionCallable, 'return');
        $metadata->returnType = $returnType ?? $phpdocReturnType ?? new Mixed_();

        return $metadata;
    }

    private function getCallableReflection(callable $callable): \ReflectionFunctionAbstract
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
        $type = $parameter->hasType() ? $typeResolver->resolve((string)$parameter->getType()) : null;
        $metadata->type = $type ?? $phpdocType ?? new Mixed_();

        return $metadata;
    }
}
