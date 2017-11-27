<?php

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;
use Tsufeki\KayoJsonMapper\Loader\ArrayLoader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\ArrayLoader
 */
class ArrayLoaderTest extends TestCase
{
    public function test_loads_array()
    {
        $type = new Types\Array_(new Types\Integer());
        $input = [1, 2, 3];
        $output = [4, 8, 12];

        $innerLoader = $this->createMock(Loader::class);
        $innerLoader
            ->expects($this->exactly(3))
            ->method('load')
            ->withConsecutive(...array_map(function ($i) use ($type) {
                return [$this->identicalTo($i), $type->getValueType()];
            }, $input))
            ->willReturnOnConsecutiveCalls(...$output);

        $arrayLoader = new ArrayLoader($innerLoader);

        $this->assertSame($output, $arrayLoader->load($input, $type));
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value($type)
    {
        $loader = $this->createMock(Loader::class);
        $arrayLoader = new ArrayLoader($loader);
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $arrayLoader->load(1, $resolver->resolve($type));
    }

    public function unsupported_types(): array
    {
        return [
            ['int'],
            [\stdClass::class],
        ];
    }

    /**
     * @dataProvider bad_type_data
     */
    public function test_throws_on_mismatched_type(string $type, $data)
    {
        $resolver = new TypeResolver();
        $innerLoader = $this->createMock(Loader::class);
        $arrayLoader = new ArrayLoader($innerLoader);

        $this->expectException(TypeMismatchException::class);
        $arrayLoader->load($data, $resolver->resolve($type));
    }

    public function bad_type_data(): array
    {
        return [
            ['int[]', 7.5],
            ['array', 'foo'],
            ['mixed[]', new \stdClass()],
            ['array', null],
        ];
    }
}
