<?php

namespace Tests\Tsufeki\KayoJsonMapper\Fixtures;

use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;

class TestClass
{
    /**
     * @var int
     */
    public $foo;

    /**
     * @var string
     */
    public $bar;

    public function __construct($foo = null, $bar = null)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public static function metadata(): ClassMetadata
    {
        $foo = new PropertyMetadata();
        $foo->name = 'foo';
        $foo->variableName = 'foo';
        $foo->type = new Types\Integer();
        $bar = new PropertyMetadata();
        $bar->name = 'barSerializedOnly';
        $bar->variableName = 'bar';
        $bar->type = new Types\String_();
        $classMetadata = new ClassMetadata();
        $classMetadata->properties = [$foo, $bar];

        return $classMetadata;
    }
}
