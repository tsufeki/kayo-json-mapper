<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\MetadataException;

interface ClassMetadataProvider
{
    /**
     * @param string $class
     *
     * @throws MetadataException
     *
     * @return Metadata\ClassMetadata
     */
    public function getClassMetadata(string $class): Metadata\ClassMetadata;
}
