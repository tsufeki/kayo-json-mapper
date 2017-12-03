<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader\Instantiator;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\ClassMappingInstantiator;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\Instantiator\ClassMappingInstantiator
 */
class ClassMappingInstantiatorTest extends TestCase
{
    public function test_maps_class()
    {
        $instantiator = new ClassMappingInstantiator();
        $instantiator->addMapping(\DateTimeInterface::class, \DateTime::class);

        $result = $instantiator->instantiate(\DateTimeInterface::class, new \stdClass());
        $this->assertInstanceOf(\DateTime::class, $result);
    }

    public function test_maps_class_with_callback()
    {
        $instantiator = new ClassMappingInstantiator();
        $data = new \stdClass();

        $callback = function (\stdClass $dataPassed) use ($data) {
            $this->assertSame($data, $dataPassed);

            return \DateTime::class;
        };

        $instantiator->addCallback(\DateTimeInterface::class, $callback);

        $result = $instantiator->instantiate(\DateTimeInterface::class, $data);
        $this->assertInstanceOf(\DateTime::class, $result);
    }
}
