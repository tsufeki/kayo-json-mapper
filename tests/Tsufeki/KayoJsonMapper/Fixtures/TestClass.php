<?php

namespace Tests\Tsufeki\KayoJsonMapper\Fixtures;

class TestClass
{
    /**
     * @var string
     */
    public $string;

    /**
     * @var int
     */
    public $int;

    public function __construct($string = null, $int = null)
    {
        $this->string = $string;
        $this->int = $int;
    }
}
