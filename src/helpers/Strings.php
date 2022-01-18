<?php

namespace Helpers;

final class Strings {

    public static function startsWith(
        string $search,
        string $subject,
        bool $strict = true
    ): bool {
        $start = substr($subject, 0, strlen($search));
        if (!$strict) {
            $start = strtolower($start);
            $search = strtolower($search);
        }

        return $start === $search;
    }

    public static function leftTrim(string $search, string $subject): string {
        if (self::startsWith($search, $subject)) {
            $subject = substr($subject, strlen($search));
        }

        return $subject;
    }
}
