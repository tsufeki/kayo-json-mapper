<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\InfiniteRecursionException;
use Tsufeki\KayoJsonMapper\Exception\MaxDepthExceededException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class DispatchingDumper implements Dumper
{
    const TYPE_MAP = [
        'array' => 'array',
        'boolean' => 'bool',
        'double' => 'float',
        'integer' => 'int',
        'NULL' => 'null',
        'object' => 'object',
        'string' => 'string',
    ];

    /**
     * @var Dumper[][]
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
        foreach ($dumper->getSupportedTypes() as $key) {
            $key = strtolower($key);
            if (!isset($this->dumpers[$key])) {
                $this->dumpers[$key] = [];
            }
            array_unshift($this->dumpers[$key], $dumper);
        }

        return $this;
    }

    public function getSupportedTypes(): array
    {
        return ['any'];
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
            foreach ($this->getDumpersForValue($value) as $dumper) {
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

    /**
     * @return Dumper[]
     */
    private function getDumpersForValue($value): array
    {
        $keys = [];
        $type = self::TYPE_MAP[gettype($value)] ?? null;

        if ($type === 'object') {
            $keys[] = '\\' . strtolower(get_class($value));
        }

        if ($type !== null) {
            $keys[] = $type;
        }

        $keys[] = 'any';

        $dumpers = [];
        foreach ($keys as $key) {
            foreach ($this->dumpers[$key] ?? [] as $dumper) {
                $dumpers[] = $dumper;
            }
        }

        return $dumpers;
    }
}
