<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader\Instantiator;

use PHPUnit\Framework\TestCase;
use Tests\Tsufeki\KayoJsonMapper\Fixtures\ThrowingConstructorClass;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\NoConstructorInstantiator;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\Instantiator\NoConstructorInstantiator
 */
class NoConstructorInstantiatorTest extends TestCase
{
    public function test_creates_new_instance()
    {
        $instantiator = new NoConstructorInstantiator();

        $result = $instantiator->instantiate(ThrowingConstructorClass::class);
        $this->assertInstanceOf(ThrowingConstructorClass::class, $result);
    }
}
