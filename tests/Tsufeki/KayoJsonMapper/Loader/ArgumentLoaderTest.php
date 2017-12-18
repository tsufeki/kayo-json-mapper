<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tsufeki\KayoJsonMapper\Exception\InvalidDataException;
use Tsufeki\KayoJsonMapper\MapperBuilder;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\ArgumentLoader
 */
class ArgumentLoaderTest extends TestCase
{
    public function test_load_arguments_assoc()
    {
        $function = function (int $foo, TestClass $bar) { };
        $data = (object)[
            'bar' => (object)[
                'foo' => 1,
                'bar' => 'baz',
            ],
            'foo' => 42,
        ];

        $mapper = MapperBuilder::create()->getMapper();
        $args = $mapper->loadArguments($data, $function);

        $this->assertEquals([42, new TestClass(1, 'baz')], $args);
    }

    public function test_load_arguments_array()
    {
        $function = function (int $foo, TestClass $bar) { };
        $data = [
            42,
            (object)[
                'foo' => 1,
                'bar' => 'baz',
            ],
        ];

        $mapper = MapperBuilder::create()->getMapper();
        $args = $mapper->loadArguments($data, $function);

        $this->assertEquals([42, new TestClass(1, 'baz')], $args);
    }

    public function test_load_arguments_missing_optional()
    {
        $function = function (int $foo, string $bar = 'x') { };
        $data = (object)[
            'foo' => 42,
        ];

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

    public function test_load_arguments_unknown_argument()
    {
        $function = function () { };
        $data = ['foo' => 42];

        $mapper = MapperBuilder::create()->getMapper();

        $this->expectException(InvalidDataException::class);
        $args = $mapper->loadArguments($data, $function);
    }

    public function test_load_arguments_variadic()
    {
        $function = function (string $foo, int ...$bar) { };
        $data = ['FOO', 5, 6, 7];

        $mapper = MapperBuilder::create()->getMapper();
        $args = $mapper->loadArguments($data, $function);

        $this->assertEquals($data, $args);
    }

    public function test_load_arguments_throws_on_bad_type()
    {
        $function = function () { };
        /** @var mixed $data */
        $data = 42;

        $mapper = MapperBuilder::create()->getMapper();

        $this->expectException(InvalidDataException::class);
        $args = $mapper->loadArguments($data, $function);
    }
}
