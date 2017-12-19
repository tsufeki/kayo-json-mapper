<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Dumper\ScalarNullDumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

/**
 * @covers \Tsufeki\KayoJsonMapper\Dumper\ScalarNullDumper
 */
class ScalarNullDumperTest extends TestCase
{
    /**
     * @dataProvider dump_data
     */
    public function test_dumps_scalar($value)
    {
        $dumper = new ScalarNullDumper();
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
        $dumper = new ScalarNullDumper();
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
