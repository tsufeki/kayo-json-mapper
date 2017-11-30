<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;
use Tsufeki\KayoJsonMapper\Loader\UnionLoader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\UnionLoader
 */
class UnionLoaderTest extends TestCase
{
    private function makeInnerLoader($type1, $type2, $value, $result)
    {
        $loader = $this->createMock(Loader::class);
        $loader
            ->expects($this->at(0))
            ->method('load')
            ->with($this->identicalTo($value), $this->equalTo($type1))
            ->willThrowException(new UnsupportedTypeException());

        $loader
            ->expects($this->at(1))
            ->method('load')
            ->with($this->identicalTo($value), $this->equalTo($type2))
            ->willReturn($result);

        return $loader;
    }

    public function test_calls_one_matching_loader()
    {
        $type = new Types\Compound([new Types\Integer(), new Types\String_(), new Types\Null_()]);
        $value = 'foo';
        $result = 'bar';

        $unionLoader = new UnionLoader($this->makeInnerLoader($type->get(0), $type->get(1), $value, $result));

        $this->assertSame($result, $unionLoader->load($value, $type, new Context()));
    }

    public function test_calls_one_matching_loader_for_nullable()
    {
        $type = new Types\Nullable(new Types\Integer());
        $value = null;
        $result = 42;

        $unionLoader = new UnionLoader($this->makeInnerLoader($type->getActualType(), new Types\Null_(), $value, $result));

        $this->assertSame($result, $unionLoader->load($value, $type, new Context()));
    }

    public function test_throws_when_no_match()
    {
        $type = new Types\Nullable(new Types\Integer());
        $value = 'foo';

        $loader = $this->createMock(Loader::class);
        $loader
            ->expects($this->exactly(2))
            ->method('load')
            ->with($this->identicalTo($value), $this->anything())
            ->willThrowException(new UnsupportedTypeException());

        $unionLoader = new UnionLoader($loader);

        $this->expectException(TypeMismatchException::class);
        $unionLoader->load($value, $type, new Context());
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value($type)
    {
        $loader = $this->createMock(Loader::class);
        $unionLoader = new UnionLoader($loader);
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $unionLoader->load(1, $resolver->resolve($type), new Context());
    }

    public function unsupported_types(): array
    {
        return [
            ['int'],
            [\stdClass::class],
        ];
    }
}
