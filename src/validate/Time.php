<?php

namespace Validate;

class Time extends \Validate\Validation {

    public array $types = ['string', 'integer', 'double'];

    public string $message = '{[?]} No es una hora válida';

    public function validate(\Validate\Validator $validator): bool {
        return preg_match(
            $this->regex('time'),
            $validator->value
        );
    }
}
