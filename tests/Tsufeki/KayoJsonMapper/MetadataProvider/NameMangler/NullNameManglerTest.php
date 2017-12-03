<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler\NullNameMangler;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler\NullNameMangler
 */
class NullNameManglerTest extends TestCase
{
    /**
     * @dataProvider data
     */
    public function test_mangle(string $name, string $expected)
    {
        $mangler = new NullNameMangler();

        $this->assertSame($expected, $mangler->mangle($name));
    }

    public function data(): array
    {
        return [
            ['fooBar', 'fooBar'],
        ];
    }
}
