<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Exception;

class InfiniteRecursionException extends MapperException
{
    public function __construct()
    {
        parent::__construct('Infinite loop encountered while dumping/loading');
    }
}
