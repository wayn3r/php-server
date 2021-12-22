<?php

namespace Utilities\Validation;

class Email extends \Utilities\Validation {
    public array $types = ['string'];
    public string $message = '{[?]} No es un correo electrónico válido';
    public function validate(\Utilities\Validator $validator): bool {
        return preg_match(
            $this->regex('email'),
            $validator->value
        );
    }
}
