<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\NameMangler;

class NullNameMangler implements NameMangler
{
    public function mangle(string $name): string
    {
        return $name;
    }
}
