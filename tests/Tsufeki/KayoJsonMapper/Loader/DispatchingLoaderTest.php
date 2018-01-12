<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\DispatchingLoader;
use Tsufeki\KayoJsonMapper\Loader\Loader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\DispatchingLoader
 */
class DispatchingLoaderTest extends TestCase
{
    public function test_calls_one_matching_loader()
    {
        $value = 42;
        $result = 7;
        $type = new String_();

        $loader1 = $this->createMock(Loader::class);
        $loader1
            ->expects($this->never())
            ->method('load');
        $loader1
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['string']);

        $loader2 = $this->createMock(Loader::class);
        $loader2
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($value), $this->identicalTo($type))
            ->willReturn($result);
        $loader2
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['string']);

        $loader3 = $this->createMock(Loader::class);
        $loader3
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($value), $this->identicalTo($type))
            ->willThrowException(new UnsupportedTypeException());
        $loader3
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['string']);

        $loader4 = $this->createMock(Loader::class);
        $loader4
            ->expects($this->never())
            ->method('load');
        $loader4
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['int']);

        $loader5 = $this->createMock(Loader::class);
        $loader5
            ->expects($this->never())
            ->method('load');
        $loader5
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['any']);

        $dispatchingLoader = new DispatchingLoader();
        $dispatchingLoader
            ->add($loader1)
            ->add($loader2)
            ->add($loader3)
            ->add($loader4)
            ->add($loader5);

        $this->assertSame($result, $dispatchingLoader->load($value, $type, new Context()));
    }

    /**
     * @dataProvider data_matching_types
     */
    public function test_dispatches_type(string $typeString, string $supportedType)
    {
        $resolver = new TypeResolver();
        $type = $resolver->resolve($typeString);
        $value = 42;
        $result = 7;

        $loader = $this->createMock(Loader::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($value), $this->identicalTo($type))
            ->willReturn($result);
        $loader
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn([$supportedType]);

        $dispatchingLoader = new DispatchingLoader();
        $dispatchingLoader->add($loader);

        $this->assertSame($result, $dispatchingLoader->load($value, $type, new Context()));
    }

    public function data_matching_types(): array
    {
        return [
            ['\\DateTime', '\\dateTime'],
            ['\\DateTime', 'object'],
            ['\\DateTime', 'any'],
            ['int[]', 'int[]'],
            ['int[]', 'array'],
            ['int|null', 'int|null'],
            ['int|null', 'union'],
            ['?int', '?int'],
            ['?int', 'union'],
            ['int', 'int'],
        ];
    }

    public function test_throws_when_no_loader_found()
    {
        $dispatchingLoader = new DispatchingLoader();
        $this->expectException(UnsupportedTypeException::class);
        $dispatchingLoader->load('', new String_(), new Context());
    }
}
