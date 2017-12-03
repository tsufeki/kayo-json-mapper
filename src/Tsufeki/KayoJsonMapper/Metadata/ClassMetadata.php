<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Metadata;

class ClassMetadata
{
    /**
     * Fully-qualified name.
     *
     * @var string
     */
    public $name;

    /**
     * @var PropertyMetadata[]
     */
    public $properties = [];

    public function __clone()
    {
        $properties = [];
        foreach ($this->properties as $property) {
            $properties[] = clone $property;
        }
        $this->properties = $properties;
    }
}
