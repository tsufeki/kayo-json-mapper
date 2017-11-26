<?php

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider;

class CachedMetadataProvider implements MetadataProvider
{
    /**
     * @var MetadataProvider
     */
    private $innerMetadataProvider;

    /**
     * @var ClassMetadata[]
     */
    private $classMetadataCache;

    public function __construct(MetadataProvider $innerMetadataProvider)
    {
        $this->innerMetadataProvider = $innerMetadataProvider;
        $this->classMetadataCache = [];
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        if (isset($this->classMetadataCache[$class])) {
            return $this->classMetadataCache[$class];
        }

        return $this->classMetadataCache[$class] = $this->innerMetadataProvider->getClassMetadata($class);
    }
}
