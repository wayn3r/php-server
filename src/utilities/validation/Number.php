<?php

namespace Utilities\Validation;

class Number extends \Utilities\Validation {
    public string $message = '{[?]} No es número';
    public function validate(\Utilities\Validator $validator): bool {
        return is_numeric($validator->value);
    }
}
