<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestCompoundClass;
use Tsufeki\KayoJsonMapper\Exception\InvalidDataException;
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
                TestCompoundClass::class,

                Helpers::makeStdClass([
                    'int_array' => [1, 2],
                    'test_class' => Helpers::makeStdClass([
                        'foo' => 1,
                        'bar' => 'b1',
                    ]),
                    'test_class_array' => [
                        Helpers::makeStdClass([
                            'foo' => 2,
                            'bar' => 'b2',
                        ]),
                        Helpers::makeStdClass([
                            'foo' => 3,
                            'bar' => 'b3',
                        ]),
                    ],
                    'test_private' => 'Foo',
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

            [
                TestClass::class . '[]',

                [
                    Helpers::makeStdClass([
                        'foo' => 1,
                        'bar' => 'baz',
                    ]),
                    Helpers::makeStdClass([
                        'foo' => 2,
                        'bar' => '',
                    ]),
                ],

                [
                    new TestClass(1, 'baz'),
                    new TestClass(2, ''),
                ],
            ],
        ];
    }

    public function test_load_arguments_assoc()
    {
        $function = function (int $foo, TestClass $bar) { };
        $data = Helpers::makeStdClass([
            'bar' => Helpers::makeStdClass([
                'foo' => 1,
                'bar' => 'baz',
            ]),
            'foo' => 42,
        ]);

        $mapper = MapperBuilder::create()->getMapper();
        $args = $mapper->loadArguments($data, $function);

        $this->assertEquals([42, new TestClass(1, 'baz')], $args);
    }

    public function test_load_arguments_array()
    {
        $function = function (int $foo, TestClass $bar) { };
        $data = [
            42,
            Helpers::makeStdClass([
                'foo' => 1,
                'bar' => 'baz',
            ]),
        ];

        $mapper = MapperBuilder::create()->getMapper();
        $args = $mapper->loadArguments($data, $function);

        $this->assertEquals([42, new TestClass(1, 'baz')], $args);
    }

    public function test_load_arguments_missing_optional()
    {
        $function = function (int $foo, string $bar = 'x') { };
        $data = Helpers::makeStdClass([
            'foo' => 42,
        ]);

        $mapper = MapperBuilder::create()->getMapper();
        $args = $mapper->loadArguments($data, $function);

        $this->assertEquals([42], $args);
    }

    public function test_load_arguments_missing_required()
    {
        $function = function (int $foo, string $bar = 'x') { };
        $data = [];

        $mapper = MapperBuilder::create()->getMapper();

        $this->expectException(InvalidDataException::class);
        $args = $mapper->loadArguments($data, $function);
    }
}
