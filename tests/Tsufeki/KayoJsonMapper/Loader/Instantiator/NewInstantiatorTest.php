<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Loader\Instantiator;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\NewInstantiator;

/**
 * @covers \Tsufeki\KayoJsonMapper\Loader\Instantiator\NewInstantiator
 */
class NewInstantiatorTest extends TestCase
{
    public function test_creates_new_instance()
    {
        $instantiator = new NewInstantiator();

        $result = $instantiator->instantiate(\DateTime::class);
        $this->assertInstanceOf(\DateTime::class, $result);
    }
}
