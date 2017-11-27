<?php

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionMetadataProvider;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionMetadataProvider
 */
class ReflectionMetadataProviderTest extends TestCase
{
    private function checkProperties(ClassMetadata $metadata, array $expected)
    {
        $this->assertCount(count($expected), $metadata->properties);

        $i = 0;
        foreach ($expected as $name => $type) {
            $property = $metadata->properties[$i++];
            $this->assertSame($name, $property->name, "Propery $name");
            $this->assertSame($type, (string)$property->type, "Property $name");
        }
    }

    public function test_class_with_uncommented_properties()
    {
        $object = new class() {
            public $foo = 7;
            public $barBaz;
        };

        $metadata = (new ReflectionMetadataProvider())
            ->getClassMetadata(get_class($object));

        $this->checkProperties($metadata, [
            'foo' => 'mixed',
            'barBaz' => 'mixed',
        ]);
    }

    public function test_class_with_doccommented_properties()
    {
        $object = new class() {
            /** @var int $foo */
            public $foo;
            /** @var string|null */
            public $barBaz;
        };

        $metadata = (new ReflectionMetadataProvider())
            ->getClassMetadata(get_class($object));

        $this->checkProperties($metadata, [
            'foo' => 'int',
            'barBaz' => 'string|null',
        ]);
    }

    public function test_throws_on_unknown_class()
    {
        $this->expectException(MetadataException::class);
        $metadata = (new ReflectionMetadataProvider())
            ->getClassMetadata('DoesntExist');
    }
}
