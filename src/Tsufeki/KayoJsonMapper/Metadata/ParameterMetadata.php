<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Metadata;

use phpDocumentor\Reflection\Type;

class ParameterMetadata
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
     * @var bool
     */
    public $optional = false;

    /**
     * @var bool
     */
    public $variadic = false;
}
