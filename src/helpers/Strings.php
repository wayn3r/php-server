<?php

namespace Helpers;

final class Strings {

    public static function cleanLineBreaks(string $text, bool $strict = true): string {
        $textArray = explode("\n", $text);
        $textArray = array_reduce(
            $textArray,
            function ($carry, $line) use ($strict) {
                if (!in_array($line, $carry)
                    && !empty($line)
                    && (preg_match('/[^\s]+/i', $line)
                    || !$strict && isset($carry[(count($carry) - 1)])
                    && preg_match('/[^\s]+/i', $carry[(count($carry) - 1)]))
                ) {
                    $line = preg_replace('/(\n+|\r+)/i', '', $line);
                    $carry[] = trim($line, "\n");
                }

                return $carry;
            },
            []
        );

        return trim(implode("\n", $textArray), "\n");
    }

    /**
     * Evalua si el string $subject inicia por el string $search
     *
     * @param string $search
     * El string a buscar
     * @param string $subject
     * Donde se realizara la busqueda
     * @param bool $strict Si True la comparación se hara case-sensitive
     * [optional]
     *
     * @return bool
     * True si encuentra el $search en el inicio del $subject,
     * si el $search es un string vacio devuelve True
     *
     * False de lo contrario
     */
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

    /**
     * Elimina el $search de la parte izquierda del $subject si lo encuentra
     *
     * @param string $search
     * El string a eliminar
     * @param string $subject
     * Donde se realizara la busqueda
     *
     * @return string
     * El $subject sin el $search si fue encontrado
     */
    public static function leftTrim(string $search, string $subject): string {
        if (self::startsWith($search, $subject)) {
            $subject = substr($subject, strlen($search));
        }

        return $subject;
    }

    public static function capitalize(string $string): string {
        $start = substr($string, 0, 1);
        $rest = substr($string, 1);
        return strtoupper($start) . strtolower($rest);
    }

    public static function uncapitalize(string $string): string {
        if (empty($string)) {
            return $string;
        }

        $start = substr($string, 0, 1);
        $rest = substr($string, 1);
        return strtolower($start) . $rest;
    }
}
