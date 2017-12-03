<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy;

interface AccessorStrategy
{
    /**
     * @param string $property
     *
     * @return string[]
     */
    public function getGetters(string $property): array;

    /**
     * @param string $property
     *
     * @return string[]
     */
    public function getSetters(string $property): array;
}
