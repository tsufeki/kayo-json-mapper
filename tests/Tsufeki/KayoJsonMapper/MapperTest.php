<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestCompoundClass;
use Tsufeki\KayoJsonMapper\MapperBuilder;

/**
 * @covers \Tsufeki\KayoJsonMapper\Mapper
 * @covers \Tsufeki\KayoJsonMapper\MapperBuilder
 */
class MapperTest extends TestCase
{
    /**
     * @dataProvider data
     */
    public function test(string $type, $dumped, $loaded)
    {
        $mapper = MapperBuilder::create()->getMapper();
        $actualLoaded = $mapper->load($dumped, $type);
        $actualDumped = $mapper->dump($loaded);

        $this->assertEquals($loaded, $actualLoaded);
        $this->assertEquals($dumped, $actualDumped);
    }

    public function data(): array
    {
        return [
            [
                TestClass::class,
                (object)[
                    'foo' => 42,
                    'bar' => 'baz',
                ],
                new TestClass(
                    42,
                    'baz'
                ),
            ],

            [
                TestCompoundClass::class,

                (object)[
                    'int_array' => [1, 2],
                    'test_class' => (object)[
                        'foo' => 1,
                        'bar' => 'b1',
                    ],
                    'test_class_array' => [
                        (object)[
                            'foo' => 2,
                            'bar' => 'b2',
                        ],
                        (object)[
                            'foo' => 3,
                            'bar' => 'b3',
                        ],
                    ],
                    'test_private' => 'Foo',
                ],

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

            [
                TestClass::class . '[]',

                [
                    (object)[
                        'foo' => 1,
                        'bar' => 'baz',
                    ],
                    (object)[
                        'foo' => 2,
                        'bar' => '',
                    ],
                ],

                [
                    new TestClass(1, 'baz'),
                    new TestClass(2, ''),
                ],
            ],

            [
                TestClass::class . '|' . TestClass::class . '[]',

                (object)[
                    'foo' => 1,
                    'bar' => 'baz',
                ],

                new TestClass(1, 'baz'),
            ],

            [
                TestClass::class . '|' . TestClass::class . '[]',

                [
                    (object)[
                        'foo' => 1,
                        'bar' => 'baz',
                    ],
                    (object)[
                        'foo' => 2,
                        'bar' => '',
                    ],
                ],

                [
                    new TestClass(1, 'baz'),
                    new TestClass(2, ''),
                ],
            ],

            [
                TestClass::class . '[]|' . TestClass::class,

                (object)[
                    'foo' => 1,
                    'bar' => 'baz',
                ],

                new TestClass(1, 'baz'),
            ],

            [
                TestClass::class . '[]|' . TestClass::class,

                [
                    (object)[
                        'foo' => 1,
                        'bar' => 'baz',
                    ],
                    (object)[
                        'foo' => 2,
                        'bar' => '',
                    ],
                ],

                [
                    new TestClass(1, 'baz'),
                    new TestClass(2, ''),
                ],
            ],
        ];
    }

    public function test_dumps_up_to_max_depth()
    {
        $mapper = MapperBuilder::create()
            ->setDumpMaxDepth(2)
            ->getMapper();

        $object = new TestCompoundClass(
            [1, 2],
            new TestClass(1, 'b1'),
            [
                new TestClass(2, 'b2'),
                new TestClass(3, 'b3'),
            ],
            'Foo'
        );

        $expected = (object)[
            'int_array' => [null, null],
            'test_class' => (object)[
                'foo' => null,
                'bar' => null,
            ],
            'test_class_array' => [null, null],
            'test_private' => 'Foo',
        ];

        $this->assertEquals($expected, $mapper->dump($object));
    }

    public function test_load_arguments()
    {
        $function = function (int $foo, string $bar) { };
        $data = [42, 'baz'];

        $mapper = MapperBuilder::create()->getMapper();
        $args = $mapper->loadArguments($data, $function);

        $this->assertEquals([42, 'baz'], $args);
    }
}
