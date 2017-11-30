<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\InfiniteRecursionException;

class Context
{
    /**
     * @var int
     */
    private $depth = 0;

    /**
     * @var object|null
     */
    private $targetObject;

    /**
     * @var array
     */
    private $objectIds;

    /**
     * @param object|null $targetObject
     */
    public function __construct($targetObject = null)
    {
        $this->targetObject = $targetObject;
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
     * @return object|null
     */
    public function getTargetObject()
    {
        return $this->targetObject;
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
        $this->targetObject = null;
    }

    public function pop()
    {
        array_pop($this->objectIds);
        $this->depth--;
    }
}
