<?php

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

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
