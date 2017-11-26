<?php

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
}
