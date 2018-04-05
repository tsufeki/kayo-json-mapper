<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Context;

use Tsufeki\KayoJsonMapper\Exception\InfiniteRecursionException;
use Tsufeki\KayoJsonMapper\Exception\MaxDepthExceededException;

class Context
{
    /**
     * @var int|float
     */
    private $maxDepth;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var array
     */
    private $objectIds;

    /**
     * @param int|float $maxDepth
     */
    public function __construct($maxDepth = INF)
    {
        $this->maxDepth = $maxDepth;
        $this->depth = 0;
        $this->objectIds = [];
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param mixed $value
     *
     * @throws InfiniteRecursionException
     * @throws MaxDepthExceededException
     */
    public function push($value = null)
    {
        if ($this->depth >= $this->maxDepth) {
            throw new MaxDepthExceededException();
        }

        if (is_object($value)) {
            $oid = spl_object_hash($value);
            if (isset($this->objectIds[$oid])) {
                throw new InfiniteRecursionException();
            }

            $this->objectIds[$oid] = true;
        } else {
            $this->objectIds[] = true;
        }

        $this->depth++;
    }

    public function pop()
    {
        array_pop($this->objectIds);
        $this->depth--;
    }
}
