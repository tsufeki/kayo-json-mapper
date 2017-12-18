<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\NameMangler;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\NameMangler\NullNameMangler;

/**
 * @covers \Tsufeki\KayoJsonMapper\NameMangler\NullNameMangler
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
