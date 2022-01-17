<?php

namespace Validate;

class Phone extends \Validate\Validation {

    public array $types = ['string', 'integer', 'double'];

    public string $message = '{[?]} No es un número de teléfono válido';

    public function validate(\Validate\Validator $validator): bool {
        return preg_match(
            $this->regex('phone'),
            $validator->value
        );
    }
}
