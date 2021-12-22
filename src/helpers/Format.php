<?php

namespace Helpers;

class Format {
    static function time($time, bool $hours24 = false) {
        if (!is_numeric($time)) return $time;

        while (strlen($time) < 4) $time = '0' . $time;
        $meridiem = $hours24 ? '' : ' AM';
        $hora = substr($time, 0, 2);
        $minuto = substr($time, 2, 2);
        if (!$hours24 && intval($hora) > 12) {
            $hora = $hora - 12;
            $hora = strlen($hora) === 1 ? '0' . $hora : $hora;
            $meridiem = ' PM';
        }
        $time = $hora . ':' . $minuto . $meridiem;
        return $time;
    }
    static function date(
        string $date,
        string $to = 'd/m/Y',
        string $from = 'Y-m-d'
    ): string {
        $_date = \DateTime::createFromFormat($from, $date);
        $to = preg_replace('/[a-z]/i', '%$0', $to);
        return strftime($to, $_date->getTimestamp());
    }
}
