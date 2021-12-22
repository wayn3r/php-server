<?php

namespace Utilities\Validation;

class Date extends \Utilities\Validation {
    public array $types = ['string'];
    public string $message = '{[?]} No es una fecha válida';
    public function validate(\Utilities\Validator $validator): bool {
        $date = explode('-', $validator->value);
        if (
            count($date) !== 3
            || strlen($date[0]) !== 4
            || strlen($date[1]) !== 2
            || strlen($date[2]) !== 2
        ) return false;

        return checkdate(
            floatval($date[1]), //mes
            floatval($date[2]), //dia
            floatval($date[0]) //año
        );
    }
}
