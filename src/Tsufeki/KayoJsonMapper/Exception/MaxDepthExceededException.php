<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

class MaxDepthExceededException extends MapperException
{
    public function __construct()
    {
        parent::__construct('Max recursion depth exceeded while dumping/loading');
    }
}
