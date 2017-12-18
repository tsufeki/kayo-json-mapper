<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\MissingPropertyException;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnknownPropertyException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\Instantiator;
use Tsufeki\KayoJsonMapper\Loader\Loader;
use Tsufeki\KayoJsonMapper\Loader\ObjectLoader;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\NameMangler\NameMangler;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\ObjectLoader
 */
class ObjectLoaderTest extends TestCase
{
    private function getObjectLoader(Loader $innerLoader): ObjectLoader
    {
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $metadataProvider
            ->method('getClassMetadata')
            ->with($this->identicalTo(TestClass::class))
            ->willReturn(TestClass::metadata());

        $instantiator = $this->createMock(Instantiator::class);
        $instantiator
            ->method('instantiate')
            ->with($this->identicalTo(TestClass::class))
            ->willReturn(new TestClass());

        $nameMangler = $this->createMock(NameMangler::class);
        $nameMangler
            ->method('mangle')
            ->willReturnCallback(function (string $name) {
                return $name === 'bar' ? 'barSerializedOnly' : $name;
            });

        return new ObjectLoader(
            $innerLoader,
            $metadataProvider,
            $instantiator,
            $nameMangler
        );
    }

    /**
     * @dataProvider load_object_data
     */
    public function test_loads_object($data)
    {
        $resolver = new TypeResolver();

        $innerLoader = $this->createMock(Loader::class);
        $innerLoader
            ->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive(
                [$this->identicalTo(42), $resolver->resolve('int')],
                [$this->identicalTo('baz'), $resolver->resolve('string')]
            )
            ->willReturnOnConsecutiveCalls(7, 'BAZ');

        $objectLoader = $this->getObjectLoader($innerLoader);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestClass::class), new Context());

        $this->assertCount(2, get_object_vars($result));
        $this->assertSame(7, $result->foo);
        $this->assertSame('BAZ', $result->bar);
    }

    public function load_object_data(): array
    {
        return [
            [(object)[
                'foo' => 42,
                'barSerializedOnly' => 'baz',
            ]],
            [[
                'foo' => 42,
                'barSerializedOnly' => 'baz',
            ]],
        ];
    }

    public function test_throws_on_unknown_property()
    {
        $resolver = new TypeResolver();

        $data = (object)[
            'foo' => 42,
            'barSerializedOnly' => 'baz',
            'unknownProperty' => true,
        ];

        $innerLoader = $this->createMock(Loader::class);

        $objectLoader = $this->getObjectLoader($innerLoader);

        $this->expectException(UnknownPropertyException::class);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestClass::class), new Context());
    }

    public function test_throws_on_missing_property()
    {
        $resolver = new TypeResolver();

        $data = (object)[
            'foo' => 42,
        ];

        $innerLoader = $this->createMock(Loader::class);
        $innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo(42), $resolver->resolve('int'))
            ->willReturn(7);

        $objectLoader = $this->getObjectLoader($innerLoader);

        $this->expectException(MissingPropertyException::class);
        $result = $objectLoader->load($data, $resolver->resolve('\\' . TestClass::class), new Context());
    }

    public function test_returns_stdClass_unchanged()
    {
        $resolver = new TypeResolver();

        $innerLoader = $this->createMock(Loader::class);
        $innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo(42), $resolver->resolve('mixed'))
            ->willReturn(42);

        $objectLoader = $this->getObjectLoader($innerLoader);

        $data = (object)[
            'foo' => 42,
        ];
        $expected = clone $data;

        $this->assertEquals($expected, $objectLoader->load($data, $resolver->resolve('\\stdClass'), new Context()));
    }

    /**
     * @dataProvider unsupported_types
     */
    public function test_throws_on_unsupported_value($type)
    {
        $innerLoader = $this->createMock(Loader::class);
        $objectLoader = $this->getObjectLoader($innerLoader);
        $resolver = new TypeResolver();

        $this->expectException(UnsupportedTypeException::class);
        $objectLoader->load(1, $resolver->resolve($type), new Context());
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
        $objectLoader = $this->getObjectLoader($innerLoader);

        $this->expectException(TypeMismatchException::class);
        $objectLoader->load($data, $resolver->resolve('object'), new Context());
    }

    public function bad_type_data(): array
    {
        return [
            [1],
            ['foo'],
            [null],
            [new \DateTime()],
        ];
    }
}
