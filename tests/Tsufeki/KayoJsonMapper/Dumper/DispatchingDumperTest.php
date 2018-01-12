<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Dumper;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Dumper\DispatchingDumper;
use Tsufeki\KayoJsonMapper\Dumper\Dumper;
use Tsufeki\KayoJsonMapper\Exception\InfiniteRecursionException;
use Tsufeki\KayoJsonMapper\Exception\MaxDepthExceededException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

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
        $dumper1
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['int']);

        $dumper2 = $this->createMock(Dumper::class);
        $dumper2
            ->expects($this->once())
            ->method('dump')
            ->with($this->identicalTo($value))
            ->willReturn($result);
        $dumper2
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['int']);

        $dumper3 = $this->createMock(Dumper::class);
        $dumper3
            ->expects($this->once())
            ->method('dump')
            ->with($this->identicalTo($value))
            ->willThrowException(new UnsupportedTypeException());
        $dumper3
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['int']);

        $dispatchingDumper = new DispatchingDumper();
        $dispatchingDumper
            ->add($dumper1)
            ->add($dumper2)
            ->add($dumper3);

        $this->assertSame($result, $dispatchingDumper->dump($value, new Context()));
    }

    public function test_calls_object_dumper()
    {
        $value = new \stdClass();
        $result = 7;

        $dumper = $this->createMock(Dumper::class);
        $dumper
            ->expects($this->once())
            ->method('dump')
            ->with($this->identicalTo($value))
            ->willReturn($result);
        $dumper
            ->expects($this->once())
            ->method('getSupportedTypes')
            ->willReturn(['\\stdClass']);

        $dispatchingDumper = new DispatchingDumper();
        $dispatchingDumper->add($dumper);

        $this->assertSame($result, $dispatchingDumper->dump($value, new Context()));
    }

    public function test_throws_when_no_dumper_found()
    {
        $dispatchingDumper = new DispatchingDumper();
        $this->expectException(UnsupportedTypeException::class);
        $dispatchingDumper->dump(1, new Context());
    }

    public function test_returns_null_when_max_depth_exceeded()
    {
        $context = $this->createMock(Context::class);
        $context
            ->expects($this->once())
            ->method('push')
            ->willThrowException(new MaxDepthExceededException());

        $dispatchingDumper = new DispatchingDumper(false);
        $this->assertNull($dispatchingDumper->dump(1, $context));
    }

    public function test_throws_on_max_depth_exceeded()
    {
        $context = $this->createMock(Context::class);
        $context
            ->expects($this->once())
            ->method('push')
            ->willThrowException(new MaxDepthExceededException());

        $dispatchingDumper = new DispatchingDumper(true);

        $this->expectException(MaxDepthExceededException::class);
        $dispatchingDumper->dump(1, $context);
    }

    public function test_returns_null_on_infinite_recursion()
    {
        $context = $this->createMock(Context::class);
        $context
            ->expects($this->once())
            ->method('push')
            ->willThrowException(new InfiniteRecursionException());

        $dispatchingDumper = new DispatchingDumper(false, false);
        $this->assertNull($dispatchingDumper->dump(1, $context));
    }

    public function test_throws_on_infinite_recursion()
    {
        $context = $this->createMock(Context::class);
        $context
            ->expects($this->once())
            ->method('push')
            ->willThrowException(new InfiniteRecursionException());

        $dispatchingDumper = new DispatchingDumper(false, true);

        $this->expectException(InfiniteRecursionException::class);
        $dispatchingDumper->dump(1, $context);
    }
}
