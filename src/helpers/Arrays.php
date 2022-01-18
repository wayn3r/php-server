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
}
