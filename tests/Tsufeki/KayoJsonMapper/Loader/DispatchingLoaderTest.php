<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader\DispatchingLoader;
use Tsufeki\KayoJsonMapper\Loader\Loader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\DispatchingLoader
 */
class DispatchingLoaderTest extends TestCase
{
    public function test_calls_one_matching_loader()
    {
        $value = 42;
        $result = 7;
        $type = new String_();

        $loader1 = $this->createMock(Loader::class);
        $loader1
            ->expects($this->never())
            ->method('load');

        $loader2 = $this->createMock(Loader::class);
        $loader2
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($value), $this->identicalTo($type))
            ->willReturn($result);

        $loader3 = $this->createMock(Loader::class);
        $loader3
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($value), $this->identicalTo($type))
            ->willThrowException(new UnsupportedTypeException());

        $dispatchingLoader = new DispatchingLoader();
        $dispatchingLoader
            ->add($loader1)
            ->add($loader2)
            ->add($loader3);

        $this->assertSame($result, $dispatchingLoader->load($value, $type, new Context()));
    }

    public function test_throws_when_no_loader_found()
    {
        $dispatchingLoader = new DispatchingLoader();
        $this->expectException(UnsupportedTypeException::class);
        $dispatchingLoader->load('', new String_(), new Context());
    }
}
