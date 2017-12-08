<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use phpDocumentor\Reflection\TypeResolver;
use Tsufeki\KayoJsonMapper\Context\ContextFactory;
use Tsufeki\KayoJsonMapper\Dumper\Dumper;
use Tsufeki\KayoJsonMapper\Loader\Loader;
use Tsufeki\KayoJsonMapper\MetadataProvider\CallableMetadataProvider;

class Mapper
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var Dumper
     */
    private $dumper;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var CallableMetadataProvider
     */
    private $callableMetadataProvider;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @see MapperBuilder
     */
    public function __construct(
        Loader $loader,
        Dumper $dumper,
        ContextFactory $contextFactory,
        CallableMetadataProvider $callableMetadataProvider
    ) {
        $this->loader = $loader;
        $this->dumper = $dumper;
        $this->contextFactory = $contextFactory;
        $this->callableMetadataProvider = $callableMetadataProvider;
        $this->typeResolver = new TypeResolver();
    }

    /**
     * Load data (such as returned by `json_decode`).
     *
     * @param mixed  $data Only stdClass, arrays and scalars.
     * @param string $type Phpdoc-like target type.
     *
     * @return mixed
     *
     * @throws Exception\InvalidDataException
     * @throws Exception\InfiniteRecursionException
     * @throws Exception\MetadataException
     * @throws Exception\UnsupportedTypeException
     */
    public function load($data, string $type)
    {
        $typeObject = $this->typeResolver->resolve($type);
        $context = $this->contextFactory->createLoadContext();

        return $this->loader->load($data, $typeObject, $context);
    }

    /**
     * Load arguments for callable.
     *
     * Does not make actual call, just returns argument values.
     *
     * @param array|\stdClass $data     Serialized arguments values either as
     *                                  an array (by position) or object (by name).
     * @param callable        $callable
     *
     * @return array Unserialized arguments as positional array.
     *
     * @throws Exception\InvalidDataException
     * @throws Exception\InfiniteRecursionException
     * @throws Exception\MetadataException
     * @throws Exception\UnsupportedTypeException
     */
    public function loadArguments($data, callable $callable): array
    {
        if (is_object($data) && $data instanceof \stdClass) {
            $data = get_object_vars($data);
        }

        if (!is_array($data)) {
            throw new Exception\InvalidDataException('Argument data must array or stdClass');
        }

        $metadata = $this->callableMetadataProvider->getCallableMetadata($callable);
        $context = $this->contextFactory->createLoadContext();

        $args = [];
        $paramPos = 0;
        $argPos = 0;
        while ($paramPos < count($metadata->parameters)) {
            $param = $metadata->parameters[$paramPos];

            if (array_key_exists($argPos, $data)) {
                $key = $argPos;
            } elseif (array_key_exists($param->name, $data)) {
                $key = $param->name;
            } else {
                if (!$param->optional) {
                    throw new Exception\InvalidDataException('Not enough arguments');
                }
                break;
            }

            $args[] = $this->loader->load($data[$key], $param->type, $context);
            unset($data[$key]);

            $argPos++;
            if (!$param->variadic) {
                $paramPos++;
            }
        }

        if (!empty($data)) {
            throw new Exception\InvalidDataException(
                'Some arguments could not be matched: ' . implode(', ', array_keys($data))
            );
        }

        return $args;
    }

    /**
     * Dump value to a respresentation suitable for `json_encode`.
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws Exception\InfiniteRecursionException
     * @throws Exception\MetadataException
     * @throws Exception\UnsupportedTypeException
     */
    public function dump($value)
    {
        $context = $this->contextFactory->createDumpContext();

        return $this->dumper->dump($value, $context);
    }
}
