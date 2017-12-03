<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy;

interface AccessorStrategy
{
    /**
     * Return possible getter names for property.
     *
     * @param string $property
     *
     * @return string[]
     */
    public function getGetters(string $property): array;

    /**
     * Return possible setter names for property.
     *
     * @param string $property
     *
     * @return string[]
     */
    public function getSetters(string $property): array;
}
