<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use Tsufeki\KayoJsonMapper\Metadata\CallableMetadata;

class CachedCallableMetadataProvider implements CallableMetadataProvider
{
    /**
     * @var CallableMetadataProvider
     */
    private $innerMetadataProvider;

    /**
     * @var CallableMetadata[]
     */
    private $callableMetadataCache;

    public function __construct(CallableMetadataProvider $innerMetadataProvider)
    {
        $this->innerMetadataProvider = $innerMetadataProvider;
        $this->callableMetadataCache = [];
    }

    public function getCallableMetadata($callable): CallableMetadata
    {
        $key = CallableUtils::getKey($callable);

        if ($key !== null) {
            if (!isset($this->callableMetadataCache[$key])) {
                $this->callableMetadataCache[$key] = $this->innerMetadataProvider->getCallableMetadata($callable);
            }

            return clone $this->callableMetadataCache[$key];
        }

        return $this->innerMetadataProvider->getCallableMetadata($callable);
    }
}
