<?php

namespace Helpers;

final class Arrays {

    static function Some(callable $callback, array $haystack): bool {
        foreach ($haystack as $key => $value)
            if ($callback($value, $key))
                return true;
        return false;
    }

    static function Find(callable $callback, array $haystack) {
        foreach ($haystack as $key => $value)
            if ($callback($value, $key))
                return $value;
        return false;
    }

    static function MapFilter(callable $callback, array $haystack): array {
        $result = [];
        foreach ($haystack as $key => $value)
            if (($value = $callback($value, $key)) !== null)
                $result[] = $value;
        return $result;
    }

    static function objectToArray(object $object): array {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        }
        return json_decode(json_encode($object), true);
    }
}
