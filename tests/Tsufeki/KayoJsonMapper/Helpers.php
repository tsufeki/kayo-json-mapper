<?php declare(strict_types=1);

namespace Tests\Tsufeki\KayoJsonMapper;

class Helpers
{
    public static function makeStdClass(array $fields): \stdClass
    {
        $result = new \stdClass();
        foreach ($fields as $name => $value) {
            $result->$name = $value;
        }

        return $result;
    }
}
