<?php

namespace Tsufeki\KayoJsonMapper\Metadata;

use phpDocumentor\Reflection\Type;

class CallableMetadata
{
    /**
     * @var ParameterMetadata[]
     */
    public $parameters = [];

    /**
     * @var Type
     */
    public $returnType;
}