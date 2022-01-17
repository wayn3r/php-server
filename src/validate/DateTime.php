<?php

namespace Validate;

class DateTime extends \Validate\Validation {

    public array $types = ['string'];

    public string $message = '{[?]} No es una fecha y hora válida';

    public function validate(\Validate\Validator $validator): bool {
        [$date, $time] = explode(' ', $validator->value);
        $date = explode('-', $date);

        return count($date) === 3
            && checkdate(
                floatval($date[1]),
                // mes
                floatval($date[2]),
                // dia
                floatval($date[0])
                // año
            )
            && preg_match(
                $this->regex('time'),
                $time
            );
    }
}
