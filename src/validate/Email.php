<?php

namespace Validate;

class Email extends \Validate\Validation {
    public array $types = ['string'];
    public string $message = '{[?]} No es un correo electrónico válido';
    public function validate(\Validate\Validator $validator): bool {
        return preg_match(
            $this->regex('email'),
            $validator->value
        );
    }
}
