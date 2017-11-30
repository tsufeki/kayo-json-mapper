<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Metadata;

use phpDocumentor\Reflection\Type;

class PropertyMetadata
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var Type
     */
    public $type;

    /**
     * @var string|null
     */
    public $variableName = null;

    /**
     * @var string|null
     */
    public $getter = null;

    /**
     * @var string|null
     */
    public $setter = null;

    public function get($object)
    {
        if ($this->getter) {
            return $object->{$this->getter}();
        }

        return $object->{$this->variableName};
    }

    public function set($object, $value): self
    {
        if ($this->setter) {
            $object->{$this->setter}($value);
        } else {
            $object->{$this->variableName} = $value;
        }

        return $this;
    }
}
