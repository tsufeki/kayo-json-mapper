<?php

namespace Tests\Tsufeki\KayoJsonMapper\Fixtures;

class TestCallables extends TestParentClass
{
    public function method(int $a): \stdClass
    {
        return new \stdClass();
    }

    /**
     * @param float $x
     * @param int   $y
     *
     * @return self
     */
    public static function commentedMethod(string $x, $y = null)
    {
    }

    public function __invoke()
    {
    }

    /**
     * @param parent $parent
     */
    public function withParent($parent)
    {
    }
}

function aFunction($x, ...$y)
{
}
