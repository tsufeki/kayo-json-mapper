<?php

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Dumper\DateTimeDumper;

/**
 * @covers \Tsufeki\KayoJsonMapper\Dumper\DateTimeDumper
 */
class DateTimeDumperTest extends TestCase
{
    /**
     * @dataProvider dump_data
     */
    public function test_dumps_datetime_to_string(\DateTime $datetime, string $result)
    {
        $dumper = new DateTimeDumper();
        $this->assertSame($result, $dumper->dump($datetime));
    }

    public function dump_data(): array
    {
        return [
            [new \DateTime('2017-11-26 23:57:00'), '2017-11-26T23:57:00+00:00'],
            [new \DateTime('2017-11-26 23:57:00+07:00'), '2017-11-26T23:57:00+07:00'],
        ];
    }
}
