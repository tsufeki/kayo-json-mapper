<?php

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tests\Tsufeki\KayoJsonMapper\Helpers;
use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Dumper\ObjectDumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

/**
 * @covers \Tsufeki\KayoJsonMapper\Dumper\ObjectDumper
 */
class ObjectDumperTest extends TestCase
{
    public function test_dumps_object()
    {
        $object = new TestClass(42, 'baz');

        $innerDumper = $this->createMock(Dumper::class);
        $innerDumper
            ->expects($this->exactly(2))
            ->method('dump')
            ->withConsecutive([$this->identicalTo(42)], [$this->identicalTo('baz')])
            ->willReturnOnConsecutiveCalls(7, 'BAZ');

        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $metadataProvider
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->identicalTo(TestClass::class))
            ->willReturn(TestClass::metadata());

        $objectDumper = new ObjectDumper($innerDumper, $metadataProvider);
        $result = $objectDumper->dump($object, new Context());

        $this->assertEquals(Helpers::makeStdClass([
            'foo' => 7,
            'barSerializedOnly' => 'BAZ',
        ]), $result);
    }

    public function test_returns_stdClass_unchanged()
    {
        $innerDumper = $this->createMock(Dumper::class);
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $dumper = new ObjectDumper($innerDumper, $metadataProvider);

        $value = Helpers::makeStdClass(['foo' => 42]);

        $result = $dumper->dump($value, new Context());
        $this->assertEquals($value, $result);
    }

    /**
     * @dataProvider bad_dump_data
     */
    public function test_throws_on_bad_value($value)
    {
        $innerDumper = $this->createMock(Dumper::class);
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $dumper = new ObjectDumper($innerDumper, $metadataProvider);
        $this->expectException(UnsupportedTypeException::class);
        $dumper->dump($value, new Context());
    }

    public function bad_dump_data(): array
    {
        return [
            ['foobar'],
            [null],
            [[]],
        ];
    }
}
