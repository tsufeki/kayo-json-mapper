<?php

namespace Tests\Tsufeki\KayoJsonMapper\Fixtures;

class TestCompoundClass
{
    /**
     * @var int[]|null
     */
    public $intArray;

    /**
     * @var ?TestClass
     */
    public $testClass;

    /**
     * @var TestClass[]
     */
    public $testClassArray;

    public function __construct($intArray = null, $testClass = null, $testClassArray = null)
    {
        $this->intArray = $intArray;
        $this->testClass = $testClass;
        $this->testClassArray = $testClassArray;
    }
}
