<?php

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

    public function __construct(Loader $loader, Dumper $dumper)
    {
        $this->loader = $loader;
        $this->dumper = $dumper;
    }

    /**
     * Load data (such as returned by `json_decode`) into object.
     *
     * @param \stdClass $data
     * @param object    $object
     */
    public function load(\stdClass $data, $object)
    {
        $type = new Types\Object_(new Fqsen('\\' . get_class($object)));

        return $this->loader->load($data, $type, $object);
    }

    /**
     * Dump object to a respresentation suitable for `json_encode`.
     *
     * @param object $object
     *
     * @return \stdClass
     */
    public function dump($object): \stdClass
    {
        return $this->dumper->dump($object);
    }

    public static function create(): self
    {
        $metadataProvider = new MetadataProvider\CachedClassMetadataProvider(
            new MetadataProvider\ReflectionClassMetadataProvider(
                new MetadataProvider\ReflectionCallableMetadataProvider(),
                new MetadataProvider\StandardAccessorStrategy()
            )
        );

        $loader = new Loader\DispatchingLoader();
        $loader
            ->add(new Loader\UnionLoader($loader))
            ->add(new Loader\MixedLoader())
            ->add(new Loader\ScalarLoader())
            ->add(new Loader\ArrayLoader($loader))
            ->add(new Loader\ObjectLoader($loader, $metadataProvider))
            ->add(new Loader\DateTimeLoader());

        $dumper = new Dumper\DispatchingDumper();
        $dumper
            ->add(new Dumper\ScalarDumper())
            ->add(new Dumper\ArrayDumper($dumper))
            ->add(new Dumper\ObjectDumper($dumper, $metadataProvider))
            ->add(new Dumper\DateTimeDumper());

        return new static($loader, $dumper);
    }
}
