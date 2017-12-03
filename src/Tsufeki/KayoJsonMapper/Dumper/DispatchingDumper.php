<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\InfiniteRecursionException;
use Tsufeki\KayoJsonMapper\Exception\MaxDepthExceededException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class DispatchingDumper implements Dumper
{
    /**
     * @var Dumper[]
     */
    private $dumpers = [];

    /**
     * @var bool
     */
    private $throwOnMaxDepthExceeded;

    /**
     * @var bool
     */
    private $throwOnInfiniteRecursion;

    public function __construct(
        bool $throwOnMaxDepthExceeded = true,
        bool $throwOnInfiniteRecursion = true
    ) {
        $this->throwOnMaxDepthExceeded = $throwOnMaxDepthExceeded;
        $this->throwOnInfiniteRecursion = $throwOnInfiniteRecursion;
    }

    public function add(Dumper $dumper): self
    {
        array_unshift($this->dumpers, $dumper);

        return $this;
    }

    public function dump($value, Context $context)
    {
        try {
            $context->push($value);
        } catch (InfiniteRecursionException $e) {
            if ($this->throwOnInfiniteRecursion) {
                throw $e;
            }

            return null;
        } catch (MaxDepthExceededException $e) {
            if ($this->throwOnMaxDepthExceeded) {
                throw $e;
            }

            return null;
        }

        try {
            foreach ($this->dumpers as $dumper) {
                try {
                    return $dumper->dump($value, $context);
                } catch (UnsupportedTypeException $e) {
                }
            }
        } finally {
            $context->pop();
        }

        throw new UnsupportedTypeException(null, $value);
    }
}
