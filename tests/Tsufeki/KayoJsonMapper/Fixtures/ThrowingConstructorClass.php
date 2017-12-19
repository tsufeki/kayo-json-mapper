<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\Fixtures;

class ThrowingConstructorClass
{
    public function __construct()
    {
        throw new \Exception('Constructor called');
    }
}
