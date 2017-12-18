<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestClass;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Dumper\Dumper;
use Tsufeki\KayoJsonMapper\Dumper\ObjectDumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\NameMangler\NameMangler;

/**
 * @covers \Tsufeki\KayoJsonMapper\Dumper\ObjectDumper
 */
class ObjectDumperTest extends TestCase
{
    private function getObjectDumper(Dumper $innerDumper): ObjectDumper
    {
        $metadataProvider = $this->createMock(ClassMetadataProvider::class);
        $metadataProvider
            ->method('getClassMetadata')
            ->with($this->identicalTo(TestClass::class))
            ->willReturn(TestClass::metadata());

        $nameMangler = $this->createMock(NameMangler::class);
        $nameMangler
            ->method('mangle')
            ->willReturnCallback(function (string $name) {
                return $name === 'bar' ? 'barSerializedOnly' : $name;
            });

        return new ObjectDumper(
            $innerDumper,
            $metadataProvider,
            $nameMangler
        );
    }

    public function test_dumps_object()
    {
        $object = new TestClass(42, 'baz');

        $innerDumper = $this->createMock(Dumper::class);
        $innerDumper
            ->expects($this->exactly(2))
            ->method('dump')
            ->withConsecutive([$this->identicalTo(42)], [$this->identicalTo('baz')])
            ->willReturnOnConsecutiveCalls(7, 'BAZ');

        $objectDumper = $this->getObjectDumper($innerDumper);
        $result = $objectDumper->dump($object, new Context());

        $this->assertEquals((object)[
            'foo' => 7,
            'barSerializedOnly' => 'BAZ',
        ], $result);
    }

    public function test_returns_stdClass_unchanged()
    {
        $innerDumper = $this->createMock(Dumper::class);
        $innerDumper
            ->expects($this->once())
            ->method('dump')
            ->with($this->identicalTo(42))
            ->willReturn(42);

        $objectDumper = $this->getObjectDumper($innerDumper);
        $value = (object)['foo' => 42];

        $result = $objectDumper->dump($value, new Context());
        $this->assertEquals($value, $result);
    }

    /**
     * @dataProvider bad_dump_data
     */
    public function test_throws_on_bad_value($value)
    {
        $innerDumper = $this->createMock(Dumper::class);
        $objectDumper = $this->getObjectDumper($innerDumper);
        $this->expectException(UnsupportedTypeException::class);
        $objectDumper->dump($value, new Context());
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
