<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;

interface ClassMetadataProvider
{
    /**
     * @param string $class
     *
     * @throws MetadataException
     *
     * @return ClassMetadata
     */
    public function getClassMetadata(string $class): ClassMetadata;
}
