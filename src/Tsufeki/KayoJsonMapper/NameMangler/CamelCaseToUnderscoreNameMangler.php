<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\NameMangler;

class CamelCaseToUnderscoreNameMangler implements NameMangler
{
    public function mangle(string $name): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }
}
