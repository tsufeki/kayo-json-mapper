<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ManglingClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler\NameMangler;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\ManglingClassMetadataProvider
 */
class ManglingClassMetadataProviderTest extends TestCase
{
    public function test_cache()
    {
        $class = 'Foo\\Bar';
        $metadata = new ClassMetadata();
        $metadata->name = $class;
        $propertyMetadata = new PropertyMetadata();
        $propertyMetadata->name = 'foo';
        $metadata->properties[] = $propertyMetadata;

        $innerProvider = $this->createMock(ClassMetadataProvider::class);
        $innerProvider
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->identicalTo($class))
            ->willReturn($metadata);

        $nameMangler = $this->createMock(NameMangler::class);
        $nameMangler
            ->expects($this->once())
            ->method('mangle')
            ->with($this->identicalTo('foo'))
            ->willReturn('bar');

        $provider = new ManglingClassMetadataProvider($innerProvider, $nameMangler);

        $mangledMetadata = $provider->getClassMetadata($class);
        $this->assertSame('bar', $mangledMetadata->properties[0]->name);
        $this->assertSame($class, $mangledMetadata->name);
    }
}
