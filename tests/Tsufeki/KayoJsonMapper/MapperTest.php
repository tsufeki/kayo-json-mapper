<?php

namespace Tests\Tsufeki\KayoJsonMapper;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tsufeki\KayoJsonMapper\Mapper;

/**
 * @covers \Tsufeki\KayoJsonMapper\Mapper
 */
class MapperTest extends TestCase
{
    /**
     * @dataProvider data
     */
    public function test($target, $dumped, $loaded)
    {
        $mapper = Mapper::create();
        $actualLoaded = $mapper->load($dumped, $target);
        $actualDumped = $mapper->dump($loaded);

        $this->assertEquals($loaded, $actualLoaded);
        $this->assertEquals($dumped, $actualDumped);
    }

    public function data(): array
    {
        return [
            [
                new TestClass(),
                Helpers::makeStdClass([
                    'foo' => 42,
                    'bar' => 'baz',
                ]),
                new TestClass(
                    42,
                    'baz'
                ),
            ],
        ];
    }
}
