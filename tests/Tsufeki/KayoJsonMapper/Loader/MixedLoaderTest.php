<?php

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Mixed_;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\MixedLoader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\MixedLoader
 */
class MixedLoaderTest extends TestCase
{
    /**
     * @dataProvider load_data
     */
    public function test_loads_mixed_data($data)
    {
        $loader = new MixedLoader();

        $this->assertSame($data, $loader->load($data, new Mixed_()));
    }

    public function load_data(): array
    {
        return [
            [7],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value($type)
    {
        $loader = new MixedLoader();
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $loader->load(1, $resolver->resolve($type));
    }

    public function unsupported_types(): array
    {
        return [
            ['int'],
            [\stdClass::class],
        ];
    }
}
