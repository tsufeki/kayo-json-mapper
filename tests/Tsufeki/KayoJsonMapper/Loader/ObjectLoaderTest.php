<?php

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tests\Tsufeki\KayoJsonMapper\Helpers;
use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;
use Tsufeki\KayoJsonMapper\Loader\ObjectLoader;
use Tsufeki\KayoJsonMapper\Context;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\ObjectLoader
 */
class ObjectLoaderTest extends TestCase
{
    public function test_loads_object()
    {
        $resolver = new TypeResolver();

        $data = Helpers::makeStdClass([
            'foo' => 42,
            'barSerializedOnly' => 'baz',
        ]);

        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $metadataProvider
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->identicalTo(TestClass::class))
            ->willReturn(TestClass::metadata());

        $innerLoader = $this->createMock(Loader::class);
        $innerLoader
            ->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive(
                [$this->identicalTo(42), $resolver->resolve('int')],
                [$this->identicalTo('baz'), $resolver->resolve('string')]
            )
            ->willReturnOnConsecutiveCalls(7, 'BAZ');

        $objectLoader = new ObjectLoader($innerLoader, $metadataProvider);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestClass::class), new Context());

        $this->assertCount(2, get_object_vars($result));
        $this->assertSame(7, $result->foo);
        $this->assertSame('BAZ', $result->bar);
    }

    public function test_returns_stdClass_unchanged()
    {
        $innerLoader = $this->createMock(Loader::class);
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $loader = new ObjectLoader($innerLoader, $metadataProvider);
        $resolver = new TypeResolver();

        $data = Helpers::makeStdClass([
            'foo' => 42,
            'bar' => 'baz',
        ]);
        $expected = clone $data;

        $this->assertEquals($expected, $loader->load($data, $resolver->resolve('\\stdClass'), new Context()));
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value($type)
    {
        $innerLoader = $this->createMock(Loader::class);
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $loader = new ObjectLoader($innerLoader, $metadataProvider);
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $loader->load(1, $resolver->resolve($type), new Context());
    }

    public function unsupported_types(): array
    {
        return [
            ['int'],
            ['array'],
            ['string[]'],
            ['mixed'],
        ];
    }

    /**
     * @dataProvider bad_type_data
     */
    public function test_throws_on_mismatched_type($data)
    {
        $resolver = new TypeResolver();
        $innerLoader = $this->createMock(Loader::class);
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $loader = new ObjectLoader($innerLoader, $metadataProvider);

        $this->expectException(TypeMismatchException::class);
        $loader->load($data, $resolver->resolve('object'), new Context());
    }

    public function bad_type_data(): array
    {
        return [
            [1],
            ['foo'],
            [null],
            [[]],
            [new \DateTime()],
        ];
    }
}
