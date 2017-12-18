<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\NameMangler;

interface NameMangler
{
    public function mangle(string $name): string;
}
