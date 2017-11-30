<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use Tsufeki\KayoJsonMapper\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;

class CachedClassMetadataProvider implements ClassMetadataProvider
{
    /**
     * @var ClassMetadataProvider
     */
    private $innerMetadataProvider;

    /**
     * @var ClassMetadata[]
     */
    private $classMetadataCache;

    public function __construct(ClassMetadataProvider $innerMetadataProvider)
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
