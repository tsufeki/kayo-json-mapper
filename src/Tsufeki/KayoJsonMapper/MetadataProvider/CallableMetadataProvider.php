<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

use Tsufeki\KayoJsonMapper\Exception\MetadataException;
use Tsufeki\KayoJsonMapper\Metadata\CallableMetadata;

interface CallableMetadataProvider
{
    /**
     * @param callable|\ReflectionFunctionAbstract $callable
     *
     * @return CallableMetadata
     *
     * @throws MetadataException
     */
    public function getCallableMetadata($callable): CallableMetadata;
}
