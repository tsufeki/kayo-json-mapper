<?php

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\MetadataException;

interface MetadataProvider
{
    /**
     * @throws MetadataException
     */
    public function getClassMetadata(string $class): Metadata\ClassMetadata;
}
