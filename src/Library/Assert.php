<?php

namespace App\Library;

class Assert
{
    /**
     * @param array<mixed> $array
     */
    public static function arrayHasPropertyOfTypeString(array $array, string $key): bool
    {
        if (key_exists($key, $array) === false) {
            return false;
        }

        return is_string($array[$key]);
    }

    /**
     * @param array<mixed> $array
     */
    public static function arrayHasPropertyOfTypeStringOrNull(array $array, string $key): bool
    {
        if (key_exists($key, $array) === false) {
            return false;
        }

        if ($array[$key] === null) {
            return true;
        }

        return is_string($array[$key]);
    }

    /**
     * @param array<mixed> $array
     */
    public static function arrayHasPropertyOfTypeStringIntOrNull(array $array, string $key): bool
    {
        if (key_exists($key, $array) === false) {
            return false;
        }

        if ($array[$key] === null) {
            return true;
        }

        if (is_int($array[$key])) {
            return true;
        }

        return is_string($array[$key]);
    }
}
