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
        $metadata = $this->callableMetadataProvider->getCallableMetadata($callable);

        if (is_object($data)) {
            $dataArray = [];

            foreach ($metadata->parameters as $param) {
                if (!property_exists($data, $param->name)) {
                    break;
                }

                $dataArray[] = $data->{$param->name};
            }
        } else {
            $dataArray = array_slice(array_values($data), 0, count($metadata->parameters));
        }

        $argCount = count($dataArray);
        if (isset($metadata->parameters[$argCount]) && !$metadata->parameters[$argCount]->optional) {
            throw new Exception\InvalidDataException('Not enough arguments');
        }

        $args = [];
        foreach ($dataArray as $i => $arg) {
            $context = $this->contextFactory->createLoadContext();
            $type = $metadata->parameters[$i]->type;
            $args[] = $this->loader->load($arg, $type, $context);
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
