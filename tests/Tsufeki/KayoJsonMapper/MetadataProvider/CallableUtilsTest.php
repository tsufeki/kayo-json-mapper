<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\TestCallables;
use Tsufeki\KayoJsonMapper\MetadataProvider\CallableUtils;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\CallableUtils
 */
class CallableUtilsTest extends TestCase
{
    /**
     * @dataProvider callable_data
     */
    public function test_get_key($callable, $expectedKey)
    {
        $this->assertSame($expectedKey, CallableUtils::getKey($callable));
    }

    public function callable_data(): array
    {
        return [
            [
                'Tests\Tsufeki\KayoJsonMapper\Fixtures\aFunction',
                'tests\tsufeki\kayojsonmapper\fixtures\afunction',
            ],
            [
                new TestCallables(),
                'tests\tsufeki\kayojsonmapper\fixtures\testcallables::__invoke',
            ],
            [
                [new TestCallables(), 'method'],
                'tests\tsufeki\kayojsonmapper\fixtures\testcallables::method',
            ],
            [
                [TestCallables::class, 'commentedMethod'],
                'tests\tsufeki\kayojsonmapper\fixtures\testcallables::commentedmethod',
            ],
            [
                'Tests\Tsufeki\KayoJsonMapper\Fixtures\TestCallables::commentedMethod',
                'tests\tsufeki\kayojsonmapper\fixtures\testcallables::commentedmethod',
            ],
            [
                new \ReflectionFunction('Tests\Tsufeki\KayoJsonMapper\Fixtures\aFunction'),
                'tests\tsufeki\kayojsonmapper\fixtures\afunction',
            ],
            [
                new \ReflectionMethod(TestCallables::class, 'method'),
                'tests\tsufeki\kayojsonmapper\fixtures\testcallables::method',
            ],
            [
                function () {},
                null,
            ],
            [
                new \ReflectionFunction(function () {}),
                null,
            ],
        ];
    }
}
