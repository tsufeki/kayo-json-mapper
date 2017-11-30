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
     * @see MapperBuilder
     */
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
        $context = new Context($object);

        return $this->loader->load($data, $type, $context);
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
        $context = new Context();

        return $this->dumper->dump($object, $context);
    }
}
