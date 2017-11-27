<?php

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Dumper\ArrayDumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

/**
 * @covers \Tsufeki\KayoJsonMapper\Dumper\ArrayDumper
 */
class ArrayDumperTest extends TestCase
{
    public function test_dumps_array()
    {
        $input = [1, 2, 3];
        $output = [4, 8, 12];

        $innerDumper = $this->createMock(Dumper::class);
        $innerDumper
            ->expects($this->exactly(3))
            ->method('dump')
            ->withConsecutive(...array_map(function ($i) { return $this->identicalTo($i); }, $input))
            ->willReturnOnConsecutiveCalls(...$output);

        $arrayDumper = new ArrayDumper($innerDumper);

        $this->assertSame($output, $arrayDumper->dump($input));
    }

    /**
     * @dataProvider bad_dump_data
     */
    public function test_throws_on_bad_value($value)
    {
        $innerDumper = $this->createMock(Dumper::class);
        $dumper = new ArrayDumper($innerDumper);
        $this->expectException(UnsupportedTypeException::class);
        $dumper->dump($value);
    }

    public function bad_dump_data(): array
    {
        return [
            ['foobar'],
            [null],
            [new \stdClass()],
        ];
    }
}
