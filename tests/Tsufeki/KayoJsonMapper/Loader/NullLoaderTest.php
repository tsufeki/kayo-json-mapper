<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\NullLoader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\NullLoader
 */
class NullLoaderTest extends TestCase
{
    /**
     * @dataProvider load_data
     */
    public function test_loads_null(string $type, bool $strict)
    {
        $resolver = new TypeResolver();
        $loader = new NullLoader($strict);

        $this->assertNull($loader->load(null, $resolver->resolve($type), new Context()));
    }

    public function load_data(): array
    {
        return [
            ['null', true],
            ['null', false],
            ['string', false],
            ['stdClass', false],
        ];
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value_strict(string $type, $data, bool $strict)
    {
        $loader = new NullLoader($strict);
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $loader->load($data, $resolver->resolve($type), new Context());
    }

    public function unsupported_types(): array
    {
        return [
            ['null', 1, false],
            ['null', 1, true],
            ['int[]', [1, 2], false],
        ];
    }

    /**
     * @dataProvider bad_type_data
     */
    public function test_throws_on_mismatched_type(string $type, bool $strict)
    {
        $resolver = new TypeResolver();
        $loader = new NullLoader($strict);

        $this->expectException(TypeMismatchException::class);
        $loader->load(null, $resolver->resolve($type), new Context());
    }

    public function bad_type_data(): array
    {
        return [
            ['int', true],
            ['stdClass', true],
        ];
    }
}
