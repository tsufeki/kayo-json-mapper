<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestCompoundClass;
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

            [
                new TestCompoundClass(),

                Helpers::makeStdClass([
                    'intArray' => [1, 2],
                    'testClass' => Helpers::makeStdClass([
                        'foo' => 1,
                        'bar' => 'b1',
                    ]),
                    'testClassArray' => [
                        Helpers::makeStdClass([
                            'foo' => 2,
                            'bar' => 'b2',
                        ]),
                        Helpers::makeStdClass([
                            'foo' => 3,
                            'bar' => 'b3',
                        ]),
                    ],
                    'testPrivate' => 'Foo',
                ]),

                new TestCompoundClass(
                    [1, 2],
                    new TestClass(1, 'b1'),
                    [
                        new TestClass(2, 'b2'),
                        new TestClass(3, 'b3'),
                    ],
                    'Foo'
                ),
            ],
        ];
    }
}
