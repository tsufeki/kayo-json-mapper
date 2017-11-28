<?php

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
     * @return self
     *
     * @throws InfiniteRecursionException
     */
    public function createNested($value = null): self
    {
        $nested = new static(null);
        $nested->depth = $this->depth + 1;
        $nested->objectIds = $this->objectIds;

        if (is_object($value)) {
            $oid = spl_object_hash($value);
            if (isset($this->objectIds[$oid])) {
                throw new InfiniteRecursionException();
            }
            $nested->objectIds[$oid] = true;
        }

        return $nested;
    }
}
