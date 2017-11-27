<?php

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedMetadataProvider;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\CachedMetadataProvider
 */
class CachedMetadataProviderTest extends TestCase
{
    public function test_cache()
    {
        $class = 'Foo\\Bar';
        $metadata = new ClassMetadata();

        $innerProvider = $this->createMock(MetadataProvider::class);
        $innerProvider
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->identicalTo($class))
            ->willReturn($metadata);

        $provider = new CachedMetadataProvider($innerProvider);

        $this->assertSame($metadata, $provider->getClassMetadata($class));
        $this->assertSame($metadata, $provider->getClassMetadata($class));
    }
}
