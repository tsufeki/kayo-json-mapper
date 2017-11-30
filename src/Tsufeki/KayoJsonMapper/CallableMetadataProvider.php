<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Exception\MetadataException;

interface CallableMetadataProvider
{
    /**
     * @param callable|\ReflectionFunctionAbstract $callable
     *
     * @return Metadata\CallableMetadata
     *
     * @throws MetadataException
     */
    public function getCallableMetadata($callable): Metadata\CallableMetadata;
}
