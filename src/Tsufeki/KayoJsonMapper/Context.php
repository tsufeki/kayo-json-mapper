<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\InfiniteRecursionException;

class Context
{
    /**
     * @var int
     */
    private $depth;

    /**
     * @var array
     */
    private $objectIds;

    public function __construct()
    {
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
     */
    public function push($value = null)
    {
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
