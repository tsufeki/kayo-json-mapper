<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\InfiniteRecursionException;
use Tsufeki\KayoJsonMapper\Exception\MaxDepthExceededException;

/**
 * @covers \Tsufeki\KayoJsonMapper\Context\Context
 */
class ContextTest extends TestCase
{
    public function test_max_depth()
    {
        $ctx = new Context();

        $this->assertSame(0, $ctx->getDepth());
        $ctx->push('foo');
        $this->assertSame(1, $ctx->getDepth());
        $ctx->push('bar');
        $this->assertSame(2, $ctx->getDepth());
        $ctx->pop();
        $this->assertSame(1, $ctx->getDepth());
        $ctx->push('foo');
        $this->assertSame(2, $ctx->getDepth());
    }

    public function test_throws_on_infinite_recursion()
    {
        $ctx = new Context();
        $foo = new \stdClass();
        $bar = new \stdClass();

        $ctx->push($foo);
        $ctx->push($bar);

        $this->expectException(InfiniteRecursionException::class);
        $ctx->push($foo);
    }

    public function test_throws_on_max_depth_exceeded()
    {
        $ctx = new Context(2);

        $ctx->push(1);
        $ctx->push(2);

        $this->expectException(MaxDepthExceededException::class);
        $ctx->push(3);
    }
}
