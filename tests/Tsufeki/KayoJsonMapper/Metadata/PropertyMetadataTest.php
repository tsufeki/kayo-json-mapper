<?php

namespace Tests\Tsufeki\KayoJsonMapper\Metadata;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;

/**
 * @covers \Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata
 */
class PropertyMetadataTest extends TestCase
{
    public function test_plain_get()
    {
        $object = new class() {
            public $foo = 42;
        };

        $md = new PropertyMetadata();
        $md->name = 'foo';

        $this->assertSame(42, $md->get($object));
    }

    public function test_getter()
    {
        $object = new class() {
            public function getFoo()
            {
                return 42;
            }
        };

        $md = new PropertyMetadata();
        $md->getter = 'getFoo';

        $this->assertSame(42, $md->get($object));
    }

    public function test_plain_set()
    {
        $object = new class() {
            public $foo = 7;
        };

        $md = new PropertyMetadata();
        $md->name = 'foo';

        $md->set($object, 42);
        $this->assertSame(42, $object->foo);
    }

    public function test_setter()
    {
        $object = new class() {
            public $__foo = 7;

            public function setFoo($value)
            {
                $this->__foo = $value;
            }
        };

        $md = new PropertyMetadata();
        $md->setter = 'setFoo';

        $md->set($object, 42);
        $this->assertSame(42, $object->__foo);
    }
}
