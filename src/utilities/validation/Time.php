<?php

namespace Utilities\Validation;

class Time extends \Utilities\Validation {
    public array $types = ['string', 'integer', 'double'];
    public string $message = '{[?]} No es una hora válida';
    public function validate(\Utilities\Validator $validator): bool {
        return preg_match(
            $this->regex('time'),
            $validator->value
        );
    }
}
