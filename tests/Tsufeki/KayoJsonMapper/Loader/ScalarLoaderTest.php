<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\ScalarLoader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\ScalarLoader
 */
class ScalarLoaderTest extends TestCase
{
    /**
     * @dataProvider load_data
     */
    public function test_loads_scalars(string $type, $data)
    {
        $resolver = new TypeResolver();
        $loader = new ScalarLoader();

        $this->assertSame($data, $loader->load($data, $resolver->resolve($type), new Context()));
    }

    public function load_data(): array
    {
        return [
            ['int', 7],
            ['string', 'foo'],
            ['float', 3.14],
            ['float', 3],
            ['bool', true],
            ['scalar', 'bar'],
        ];
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value($type)
    {
        $loader = new ScalarLoader();
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $loader->load(1, $resolver->resolve($type), new Context());
    }

    public function unsupported_types(): array
    {
        return [
            ['object'],
            [\stdClass::class],
            ['array'],
            ['int[]'],
            ['mixed'],
            ['null'],
        ];
    }

    /**
     * @dataProvider bad_type_data
     */
    public function test_throws_on_mismatched_type(string $type, $data)
    {
        $resolver = new TypeResolver();
        $loader = new ScalarLoader();

        $this->expectException(TypeMismatchException::class);
        $loader->load($data, $resolver->resolve($type), new Context());
    }

    public function bad_type_data(): array
    {
        return [
            ['int', 7.5],
            ['int', 'foo'],
            ['int', new \stdClass()],
            ['string', 3],
            ['string', null],
            ['float', false],
            ['bool', 1],
            ['scalar', new \stdClass()],
        ];
    }

    /**
     * @dataProvider load_convert_float_data
     */
    public function test_converts_float_to_int($data, $expected)
    {
        $resolver = new TypeResolver();
        $loader = new ScalarLoader(true);

        $this->assertSame($expected, $loader->load($data, $resolver->resolve('int'), new Context()));
    }

    public function load_convert_float_data(): array
    {
        return [
            [7, 7],
            [7.0, 7],
            [7.3, 7],
            [-7.3, -7],
            [1e20, PHP_INT_MAX],
            [-1e20, PHP_INT_MIN],
            [INF, PHP_INT_MAX],
            [-INF, PHP_INT_MIN],
        ];
    }
}
