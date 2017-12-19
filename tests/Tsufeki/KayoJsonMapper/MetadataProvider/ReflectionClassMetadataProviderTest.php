<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\StandardAccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc\PhpdocTypeExtractor;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionCallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionClassMetadataProvider;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionClassMetadataProvider
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc\PhpdocTypeExtractor
 */
class ReflectionClassMetadataProviderTest extends TestCase
{
    private function getProvider(): ReflectionClassMetadataProvider
    {
        $phpdocTypeExtractor = new PhpdocTypeExtractor();

        return new ReflectionClassMetadataProvider(
            new ReflectionCallableMetadataProvider($phpdocTypeExtractor),
            new StandardAccessorStrategy(),
            $phpdocTypeExtractor
        );
    }

    private function checkProperties(ClassMetadata $metadata, array $expected)
    {
        $this->assertCount(count($expected), $metadata->properties);

        $i = 0;
        foreach ($expected as $name => $data) {
            $property = $metadata->properties[$i++];
            if (!is_array($data)) {
                $data = ['type' => $data];
            }

            $this->assertSame($name, $property->name, "Property $name");
            $this->assertSame($data['type'], (string)$property->type, "Property $name");
            $this->assertSame($data['getter'] ?? null, $property->getter, "Property $name");
            $this->assertSame($data['setter'] ?? null, $property->setter, "Property $name");
            $this->assertSame($data['required'] ?? false, $property->required, "Property $name");
        }
    }

    public function test_class_with_uncommented_properties()
    {
        $object = new class() {
            public $foo = 7;
            public $barBaz;
        };

        $metadata = $this->getProvider()->getClassMetadata(get_class($object));

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

        $metadata = $this->getProvider()->getClassMetadata(get_class($object));

        $this->checkProperties($metadata, [
            'foo' => ['type' => 'int', 'required' => true],
            'barBaz' => 'string|null',
        ]);
    }

    public function test_class_with_accessors()
    {
        $object = new class() {
            private $foo;

            public function getFoo(): int
            {
                return $this->foo;
            }

            public function setFoo($foo)
            {
                $this->foo = $foo;
            }
        };

        $metadata = $this->getProvider()->getClassMetadata(get_class($object));

        $this->checkProperties($metadata, [
            'foo' => [
                'type' => 'int',
                'getter' => 'getFoo',
                'setter' => 'setFoo',
                'required' => true,
            ],
        ]);
    }

    public function test_ignores_static_properties()
    {
        $object = new class() {
            public $foo = 7;
            public static $barBaz;
        };

        $metadata = $this->getProvider()->getClassMetadata(get_class($object));

        $this->checkProperties($metadata, [
            'foo' => 'mixed',
        ]);
    }

    public function test_throws_on_unknown_class()
    {
        $this->expectException(MetadataException::class);
        $this->getProvider()->getClassMetadata('DoesntExist');
    }
}
