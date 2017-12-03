<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Exception\BadDateTimeFormatException;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\DateTimeLoader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\DateTimeLoader
 */
class DateTimeLoaderTest extends TestCase
{
    /**
     * @dataProvider load_data
     */
    public function test_loads_datetime($data, \DateTime $expected)
    {
        $loader = new DateTimeLoader();
        $resolver = new TypeResolver();

        $this->assertEquals($expected, $loader->load($data, $resolver->resolve('\\DateTime'), new Context()));
    }

    public function load_data(): array
    {
        return [
            ['2017-11-27T16:30:11+01:00', new \DateTime('2017-11-27 16:30:11+01:00')],
        ];
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value($type)
    {
        $loader = new DateTimeLoader();
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $loader->load('', $resolver->resolve($type), new Context());
    }

    public function unsupported_types(): array
    {
        return [
            ['string'],
            ['object'],
            [\stdClass::class],
            ['array'],
            ['int[]'],
            ['mixed'],
        ];
    }

    /**
     * @dataProvider bad_type_data
     */
    public function test_throws_on_mismatched_type($data)
    {
        $resolver = new TypeResolver();
        $loader = new DateTimeLoader();

        $this->expectException(TypeMismatchException::class);
        $loader->load($data, $resolver->resolve('\\DateTime'), new Context());
    }

    public function bad_type_data(): array
    {
        return [
            [1],
            [null],
            [[]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider bad_format_data
     */
    public function test_throws_on_bad_format($data)
    {
        $resolver = new TypeResolver();
        $loader = new DateTimeLoader();

        $this->expectException(BadDateTimeFormatException::class);
        $loader->load($data, $resolver->resolve('\\DateTime'), new Context());
    }

    public function bad_format_data(): array
    {
        return [
            ['foobar'],
        ];
    }
}
