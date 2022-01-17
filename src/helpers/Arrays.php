<?php

namespace Helpers;

final class Arrays {

    public static function some(callable $callback, array $haystack): bool {
        foreach ($haystack as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }

        return false;
    }

    public static function find(callable $callback, array $haystack) {
        foreach ($haystack as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return false;
    }

    public static function mapFilter(callable $callback, array $haystack): array {
        $result = [];
        foreach ($haystack as $key => $value) {
            $value = $callback($value, $key);
            if ($value !== null) {
                $result[] = $value;
            }
        }

        return $result;
    }

    public static function objectToArray(object $object): array {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }

        return json_decode(json_encode($object), true);
    }
}
