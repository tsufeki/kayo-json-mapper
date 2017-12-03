<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use Tsufeki\KayoJsonMapper\Metadata\ClassMetadata;
use Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler\NameMangler;

class ManglingClassMetadataProvider implements ClassMetadataProvider
{
    /**
     * @var ClassMetadataProvider
     */
    private $innerMetadataProvider;

    /**
     * @var NameMangler
     */
    private $nameMangler;

    public function __construct(ClassMetadataProvider $innerMetadataProvider, NameMangler $nameMangler)
    {
        $this->innerMetadataProvider = $innerMetadataProvider;
        $this->nameMangler = $nameMangler;
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        $metadata = $this->innerMetadataProvider->getClassMetadata($class);

        foreach ($metadata->properties as $property) {
            $property->name = $this->nameMangler->mangle($property->name);
        }

        return $metadata;
    }
}
