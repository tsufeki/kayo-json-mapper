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
    public $getter = null;

    /**
     * @var string|null
     */
    public $setter = null;
}
