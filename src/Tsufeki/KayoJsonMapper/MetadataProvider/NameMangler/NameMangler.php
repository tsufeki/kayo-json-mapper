<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler;

interface NameMangler
{
    public function mangle(string $name): string;
}
