<?php

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\MetadataException;

interface CallableMetadataProvider
{
    /**
     * @throws MetadataException
     */
    public function getCallableMetadata(callable $callable): Metadata\CallableMetadata;
}
