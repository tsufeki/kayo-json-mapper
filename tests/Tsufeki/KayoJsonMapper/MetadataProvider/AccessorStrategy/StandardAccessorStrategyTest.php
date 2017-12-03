<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\StandardAccessorStrategy;

/**
 * @covers \Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\StandardAccessorStrategy
 */
class StandardAccessorStrategyTest extends TestCase
{
    public function test_getters()
    {
        $strategy = new StandardAccessorStrategy();

        $this->assertSame(['getFoo', 'isFoo', 'foo'], $strategy->getGetters('foo'));
    }

    public function test_setters()
    {
        $strategy = new StandardAccessorStrategy();

        $this->assertSame(['setFoo', 'foo'], $strategy->getSetters('foo'));
    }
}
