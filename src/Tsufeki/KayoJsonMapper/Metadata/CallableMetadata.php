<?php declare(strict_types=1);

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

    public function __clone()
    {
        $parameters = [];
        foreach ($this->parameters as $parameter) {
            $parameters[] = clone $parameter;
        }
        $this->parameters = $parameters;
    }
}
