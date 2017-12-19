<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper\PropertyAccess;

use PHPUnit\Framework\TestCase;
use Tsufeki\KayoJsonMapper\Metadata\PropertyMetadata;
use Tsufeki\KayoJsonMapper\PropertyAccess\PrivatePropertyAccess;
use Tsufeki\KayoJsonMapper\PropertyAccess\PropertyAccess;
use Tsufeki\KayoJsonMapper\PropertyAccess\PublicPropertyAccess;

/**
 * @covers \Tsufeki\KayoJsonMapper\PropertyAccess\PrivatePropertyAccess
 * @covers \Tsufeki\KayoJsonMapper\PropertyAccess\PublicPropertyAccess
 */
class PropertyAccessTest extends TestCase
{
    /**
     * @dataProvider accessors_data
     */
    public function test_plain_get(PropertyAccess $propertyAccess)
    {
        $object = new class() {
            public $foo = 42;
        };

        $md = new PropertyMetadata();
        $md->name = 'foo';

        $this->assertSame(42, $propertyAccess->get($object, $md));
    }

    /**
     * @dataProvider accessors_data
     */
    public function test_getter(PropertyAccess $propertyAccess)
    {
        $object = new class() {
            public function getFoo()
            {
                return 42;
            }
        };

        $md = new PropertyMetadata();
        $md->getter = 'getFoo';

        $this->assertSame(42, $propertyAccess->get($object, $md));
    }

    /**
     * @dataProvider accessors_data
     */
    public function test_plain_set(PropertyAccess $propertyAccess)
    {
        $object = new class() {
            public $foo = 7;
        };

        $md = new PropertyMetadata();
        $md->name = 'foo';

        $propertyAccess->set($object, $md, 42);
        $this->assertSame(42, $object->foo);
    }

    /**
     * @dataProvider accessors_data
     */
    public function test_setter(PropertyAccess $propertyAccess)
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

        $propertyAccess->set($object, $md, 42);
        $this->assertSame(42, $object->__foo);
    }

    public function accessors_data(): array
    {
        return [
            [new PublicPropertyAccess()],
            [new PrivatePropertyAccess()],
        ];
    }

    public function test_private_get()
    {
        $propertyAccess = new PrivatePropertyAccess();

        $object = new class() {
            private $foo = 42;
        };

        $md = new PropertyMetadata();
        $md->name = 'foo';

        $this->assertSame(42, $propertyAccess->get($object, $md));
    }

    public function test_private_set()
    {
        $propertyAccess = new PrivatePropertyAccess();

        $object = new class() {
            private $foo = 7;

            public function accessFoo()
            {
                return $this->foo;
            }
        };

        $md = new PropertyMetadata();
        $md->name = 'foo';

        $propertyAccess->set($object, $md, 42);
        $this->assertSame(42, $object->accessFoo());
    }
}
