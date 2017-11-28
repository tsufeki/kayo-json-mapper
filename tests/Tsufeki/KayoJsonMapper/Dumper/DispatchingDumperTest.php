<?php

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Dumper;
use Tsufeki\KayoJsonMapper\Dumper\DispatchingDumper;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Context;

/**
 * @covers \Tsufeki\KayoJsonMapper\Dumper\DispatchingDumper
 */
class DispatchingDumperTest extends TestCase
{
    public function test_calls_one_matching_dumper()
    {
        $value = 42;
        $result = 7;

        $dumper1 = $this->createMock(Dumper::class);
        $dumper1
            ->expects($this->never())
            ->method('dump');

        $dumper2 = $this->createMock(Dumper::class);
        $dumper2
            ->expects($this->once())
            ->method('dump')
            ->with($this->identicalTo($value))
            ->willReturn($result);

        $dumper3 = $this->createMock(Dumper::class);
        $dumper3
            ->expects($this->once())
            ->method('dump')
            ->with($this->identicalTo($value))
            ->willThrowException(new UnsupportedTypeException());

        $dispatchingDumper = new DispatchingDumper();
        $dispatchingDumper
            ->add($dumper1)
            ->add($dumper2)
            ->add($dumper3);

        $this->assertSame($result, $dispatchingDumper->dump($value, new Context()));
    }

    public function test_throws_when_no_dumper_found()
    {
        $dispatchingDumper = new DispatchingDumper();
        $this->expectException(UnsupportedTypeException::class);
        $dispatchingDumper->dump(1, new Context());
    }
}
