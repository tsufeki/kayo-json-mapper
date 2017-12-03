<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types;

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
     * @var CallableMetadataProvider
     */
    private $callableMetadataProvider;

    /**
     * @see MapperBuilder
     */
    public function __construct(Loader $loader, Dumper $dumper, CallableMetadataProvider $callableMetadataProvider)
    {
        $this->loader = $loader;
        $this->dumper = $dumper;
        $this->callableMetadataProvider = $callableMetadataProvider;
    }

    /**
     * Load data (such as returned by `json_decode`) into object.
     *
     * @param \stdClass $data
     * @param object    $object
     *
     * @return object
     *
     * @throws Exception\InfiniteRecursionException
     * @throws Exception\InvalidDataException
     * @throws Exception\MetadataException
     * @throws Exception\UnsupportedTypeException
     */
    public function load(\stdClass $data, $object)
    {
        $type = new Types\Object_(new Fqsen('\\' . get_class($object)));
        $context = new Context($object);

        return $this->loader->load($data, $type, $context);
    }

    /**
     * @param array  $data
     * @param string $class Class of the elements.
     *
     * @return object[]
     *
     * @throws Exception\InvalidDataException
     * @throws Exception\InfiniteRecursionException
     * @throws Exception\MetadataException
     * @throws Exception\UnsupportedTypeException
     */
    public function loadArray(array $data, string $class)
    {
        $type = new Types\Array_(new Types\Object_(new Fqsen('\\' . $class)));
        $context = new Context();

        return $this->loader->load($data, $type, $context);
    }

    /**
     * @param array|\stdClass $data     Serialized arguments values as sequencial array or associative object.
     * @param callable        $callable
     *
     * @return array Unserialized, sequencial arguments.
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
            $context = new Context();
            $type = $metadata->parameters[$i]->type;
            $args[] = $this->loader->load($arg, $type, $context);
        }

        return $args;
    }

    /**
     * Dump object to a respresentation suitable for `json_encode`.
     *
     * @param object $object
     *
     * @return \stdClass
     *
     * @throws Exception\InfiniteRecursionException
     * @throws Exception\MetadataException
     * @throws Exception\UnsupportedTypeException
     */
    public function dump($object): \stdClass
    {
        $context = new Context();

        return $this->dumper->dump($object, $context);
    }
}
