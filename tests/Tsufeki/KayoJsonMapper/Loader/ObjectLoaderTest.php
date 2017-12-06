<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestParentClass;
use Tests\Tsufeki\KayoJsonMapper\Helpers;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\MissingPropertyException;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnknownPropertyException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\Instantiator;
use Tsufeki\KayoJsonMapper\Loader\Loader;
use Tsufeki\KayoJsonMapper\Loader\ObjectLoader;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;

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

        $instantiator = $this->createMock(Instantiator::class);
        $instantiator
            ->expects($this->once())
            ->method('instantiate')
            ->with($this->identicalTo(TestClass::class), $this->identicalTo($data))
            ->willReturn(new TestClass());

        $objectLoader = new ObjectLoader($innerLoader, $metadataProvider, $instantiator);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestClass::class), new Context());

        $this->assertCount(2, get_object_vars($result));
        $this->assertSame(7, $result->foo);
        $this->assertSame('BAZ', $result->bar);
    }

    public function test_loads_object_when_a_subclass_is_instantiated()
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

        $instantiator = $this->createMock(Instantiator::class);
        $instantiator
            ->expects($this->once())
            ->method('instantiate')
            ->with($this->identicalTo(TestParentClass::class), $this->identicalTo($data))
            ->willReturn(new TestClass());

        $objectLoader = new ObjectLoader($innerLoader, $metadataProvider, $instantiator);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestParentClass::class), new Context());

        $this->assertCount(2, get_object_vars($result));
        $this->assertSame(7, $result->foo);
        $this->assertSame('BAZ', $result->bar);
    }

    public function test_throws_on_unknown_property()
    {
        $resolver = new TypeResolver();

        $data = Helpers::makeStdClass([
            'foo' => 42,
            'barSerializedOnly' => 'baz',
            'unknownProperty' => true,
        ]);

        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $metadataProvider
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->identicalTo(TestClass::class))
            ->willReturn(TestClass::metadata());

        $innerLoader = $this->createMock(Loader::class);

        $instantiator = $this->createMock(Instantiator::class);
        $instantiator
            ->expects($this->once())
            ->method('instantiate')
            ->with($this->identicalTo(TestClass::class), $this->identicalTo($data))
            ->willReturn(new TestClass());

        $objectLoader = new ObjectLoader($innerLoader, $metadataProvider, $instantiator);

        $this->expectException(UnknownPropertyException::class);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestClass::class), new Context());
    }

    public function test_throws_on_missing_property()
    {
        $resolver = new TypeResolver();

        $data = Helpers::makeStdClass([
            'foo' => 42,
        ]);

        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $metadataProvider
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->identicalTo(TestClass::class))
            ->willReturn(TestClass::metadata());

        $innerLoader = $this->createMock(Loader::class);
        $innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo(42), $resolver->resolve('int'))
            ->willReturn(7);

        $instantiator = $this->createMock(Instantiator::class);
        $instantiator
            ->expects($this->once())
            ->method('instantiate')
            ->with($this->identicalTo(TestClass::class), $this->identicalTo($data))
            ->willReturn(new TestClass());

        $objectLoader = new ObjectLoader($innerLoader, $metadataProvider, $instantiator);

        $this->expectException(MissingPropertyException::class);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestClass::class), new Context());
    }

    public function test_returns_stdClass_unchanged()
    {
        $innerLoader = $this->createMock(Loader::class);
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $instantiator = $this->createMock(Instantiator::class);
        $loader = new ObjectLoader($innerLoader, $metadataProvider, $instantiator);
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
        $instantiator = $this->createMock(Instantiator::class);
        $loader = new ObjectLoader($innerLoader, $metadataProvider, $instantiator);
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
        $instantiator = $this->createMock(Instantiator::class);
        $loader = new ObjectLoader($innerLoader, $metadataProvider, $instantiator);

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
