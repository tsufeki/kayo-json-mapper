<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Metadata\CallableMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedCallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\CallableMetadataProvider;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\CachedCallableMetadataProvider
 */
class CachedCallableMetadataProviderTest extends TestCase
{
    public function test_cache()
    {
        $callable = 'fooBar';
        $metadata = new CallableMetadata();
        $metadata->returnType = new Object_();

        $innerProvider = $this->createMock(CallableMetadataProvider::class);
        $innerProvider
            ->expects($this->once())
            ->method('getCallableMetadata')
            ->with($this->identicalTo($callable))
            ->willReturn($metadata);

        $provider = new CachedCallableMetadataProvider($innerProvider);

        $this->assertEquals($metadata, $provider->getCallableMetadata($callable));
        $this->assertEquals($metadata, $provider->getCallableMetadata($callable));
    }

    public function test_does_not_cache_closures()
    {
        $callable = function () {};
        $metadata = new CallableMetadata();
        $metadata->returnType = new Object_();

        $innerProvider = $this->createMock(CallableMetadataProvider::class);
        $innerProvider
            ->expects($this->exactly(2))
            ->method('getCallableMetadata')
            ->with($this->identicalTo($callable))
            ->willReturn($metadata);

        $provider = new CachedCallableMetadataProvider($innerProvider);

        $this->assertEquals($metadata, $provider->getCallableMetadata($callable));
        $this->assertEquals($metadata, $provider->getCallableMetadata($callable));
    }
}
