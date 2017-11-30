<?php declare(strict_types=1);

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

    /**
     * @var string
     */
    private $testPrivate;

    public function __construct($intArray = null, $testClass = null, $testClassArray = null, $testPrivate = null)
    {
        $this->intArray = $intArray;
        $this->testClass = $testClass;
        $this->testClassArray = $testClassArray;
        $this->testPrivate = $testPrivate;
    }

    public function getTestPrivate()
    {
        return $this->testPrivate;
    }

    public function setTestPrivate(string $testPrivate)
    {
        $this->testPrivate = $testPrivate;

        return $this;
    }
}
