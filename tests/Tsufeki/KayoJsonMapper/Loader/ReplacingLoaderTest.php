<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\TypeResolver;
use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Loader\Loader;
use Tsufeki\KayoJsonMapper\Loader\ReplacingLoader;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\ReplacingLoader
 */
class ReplacingLoaderTest extends TestCase
{
    /**
     * @dataProvider data_types
     */
    public function test_replaces_type(string $inputType, string $expectedType)
    {
        $resolver = new TypeResolver();
        $value = new \stdClass();
        $result = new \stdClass();

        $loader = $this->createMock(Loader::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($value), $this->equalTo($resolver->resolve($expectedType)))
            ->willReturn($result);

        $replacingLoader = new ReplacingLoader($loader);
        $replacingLoader->replaceType('int', 'stdClass');

        $this->assertSame($result, $replacingLoader->load($value, $resolver->resolve($inputType), new Context()));
    }

    public function data_types(): array
    {
        return [
            ['int', '\\stdClass'],
            ['string', 'string'],
            ['\\stdClass', '\\stdClass'],
        ];
    }

    public function test_replaces_type_callback()
    {
        $inputType = 'int';
        $outputType = 'string';
        $resolver = new TypeResolver();
        $value = new \stdClass();
        $result = new \stdClass();

        $loader = $this->createMock(Loader::class);
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($value), $this->equalTo($resolver->resolve($outputType)))
            ->willReturn($result);

        $replacingLoader = new ReplacingLoader($loader);
        $replacingLoader->replaceTypeCallback('int', function ($data, $type) use ($value, $inputType, $outputType) {
            $this->assertSame($value, $data);
            $this->assertSame($type, $inputType);

            return $outputType;
        });

        $this->assertSame($result, $replacingLoader->load($value, $resolver->resolve($inputType), new Context()));
    }
}
