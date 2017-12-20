<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\ArrayLoader;
use Tsufeki\KayoJsonMapper\Loader\Loader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\ArrayLoader
 */
class ArrayLoaderTest extends TestCase
{
    /**
     * @dataProvider load_array_data
     */
    public function test_loads_array($input, bool $acceptStdClass = false)
    {
        $type = new Types\Array_(new Types\Integer());
        $output = [4, 8, 12];

        $innerLoader = $this->createMock(Loader::class);
        $innerLoader
            ->expects($this->exactly(3))
            ->method('load')
            ->withConsecutive(...array_map(function ($i) use ($type) {
                return [$this->identicalTo($i), $type->getValueType()];
            }, (array)$input))
            ->willReturnOnConsecutiveCalls(...$output);

        $arrayLoader = new ArrayLoader($innerLoader, $acceptStdClass);

        $this->assertSame($output, $arrayLoader->load($input, $type, new Context()));
    }

    public function load_array_data(): array
    {
        return [
            [[1, 2, 3]],
            [(object)[1, 2, 3], true],
        ];
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
        $arrayLoader->load(1, $resolver->resolve($type), new Context());
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
        $arrayLoader->load($data, $resolver->resolve($type), new Context());
    }

    public function bad_type_data(): array
    {
        return [
            ['int[]', 7.5],
            ['array', 'foo'],
            ['mixed[]', new \DateTime()],
            ['array', null],
        ];
    }
}
