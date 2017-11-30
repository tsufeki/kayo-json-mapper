<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\MetadataException;

interface ClassMetadataProvider
{
    /**
     * @throws MetadataException
     */
    public function getClassMetadata(string $class): Metadata\ClassMetadata;
}
