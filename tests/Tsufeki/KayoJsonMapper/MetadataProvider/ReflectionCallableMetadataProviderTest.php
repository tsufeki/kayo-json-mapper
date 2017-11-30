<?php

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestCallables;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestParentClass;
use Tsufeki\KayoJsonMapper\Metadata\CallableMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\PhpdocTypeExtractor;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionCallableMetadataProvider;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionCallableMetadataProvider
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\PhpdocTypeExtractor
 */
class ReflectionCallableMetadataProviderTest extends TestCase
{
    private function getProvider(): ReflectionCallableMetadataProvider
    {
        return new ReflectionCallableMetadataProvider(
            new PhpdocTypeExtractor()
        );
    }

    private function checkSignature(CallableMetadata $metadata, array $expected)
    {
        $this->assertSame($expected['return'], (string)$metadata->returnType);
        unset($expected['return']);

        $this->assertCount(count($expected), $metadata->parameters);

        $i = 0;
        foreach ($expected as $name => $data) {
            $param = $metadata->parameters[$i++];
            if (!is_array($data)) {
                $data = ['type' => $data];
            }

            $this->assertSame($name, $param->name, "Parameter $name");
            $this->assertSame($data['type'], (string)$param->type, "Parameter $name");
            $this->assertSame($data['optional'] ?? false, $param->optional, "Parameter $name");
            $this->assertSame($data['variadic'] ?? false, $param->variadic, "Parameter $name");
        }
    }

    /**
     * @dataProvider callables
     */
    public function test_callable(callable $callable, array $expected)
    {
        $metadata = $this->getProvider()->getCallableMetadata($callable);

        $this->checkSignature($metadata, $expected);
    }

    public function callables(): array
    {
        return [
            [
                [new TestCallables(), 'method'],
                [
                    'a' => 'int',
                    'return' => '\\stdClass',
                ],
            ],

            [
                TestCallables::class . '::commentedMethod',
                [
                    'x' => 'string',
                    'y' => ['type' => 'int', 'optional' => true],
                    'return' => '\\' . TestCallables::class,
                ],
            ],

            [
                new TestCallables(),
                [
                    'return' => 'mixed',
                ],
            ],

            [
                'Tests\\Tsufeki\\KayoJsonMapper\\Fixtures\\aFunction',
                [
                    'x' => 'mixed',
                    'y' => ['type' => 'mixed', 'variadic' => true, 'optional' => true],
                    'return' => 'mixed',
                ],
            ],

            [
                function (int $foo): string { },
                [
                    'foo' => 'int',
                    'return' => 'string',
                ],
            ],

            [
                [new TestCallables(), 'withParent'],
                [
                    'parent' => '\\' . TestParentClass::class,
                    'return' => 'mixed',
                ],
            ],
        ];
    }
}
