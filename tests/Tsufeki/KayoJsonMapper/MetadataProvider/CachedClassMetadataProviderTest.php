<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedClassMetadataProvider;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\CachedClassMetadataProvider
 */
class CachedClassMetadataProviderTest extends TestCase
{
    public function test_cache()
    {
        $class = 'Foo\\Bar';
        $metadata = new ClassMetadata();

        $innerProvider = $this->createMock(ClassMetadataProvider::class);
        $innerProvider
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->identicalTo($class))
            ->willReturn($metadata);

        $provider = new CachedClassMetadataProvider($innerProvider);

        $this->assertSame($metadata, $provider->getClassMetadata($class));
        $this->assertSame($metadata, $provider->getClassMetadata($class));
    }
}
