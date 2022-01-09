<?php

namespace Validate;

class Password extends \Validate\Validation {
    private int $passwordMinLength = 4;
    private int $passwordMaxLength = 16;
    public array $types = ['string'];
    public string $message = 'El password no es válido';

    public function validate(\Validate\Validator $validator): bool {
        $password = $validator->value;
        $passLength = mb_strlen($password);

        if (
            $passLength < $this->passwordMinLength
            || $passLength > $this->passwordMaxLength
            || trim($password) !== $password
            || ctype_alpha($password)
            || ctype_digit($password)
        )
            return FALSE;

        return TRUE;
    }
}
