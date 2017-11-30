<?php

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Dumper\ScalarDumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

/**
 * @covers \Tsufeki\KayoJsonMapper\Dumper\ScalarDumper
 */
class ScalarDumperTest extends TestCase
{
    /**
     * @dataProvider dump_data
     */
    public function test_dumps_scalar($value)
    {
        $dumper = new ScalarDumper();
        $this->assertSame($value, $dumper->dump($value, new Context()));
    }

    public function dump_data(): array
    {
        return [
            [1],
            [1.5],
            [true],
            [null],
            ['foo'],
        ];
    }

    /**
     * @dataProvider bad_dump_data
     */
    public function test_throws_on_bad_value($value)
    {
        $dumper = new ScalarDumper();
        $this->expectException(UnsupportedTypeException::class);
        $dumper->dump($value, new Context());
    }

    public function bad_dump_data(): array
    {
        return [
            [[]],
            [new \stdClass()],
        ];
    }
}
