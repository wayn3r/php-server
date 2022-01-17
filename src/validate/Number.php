<?php

namespace Validate;

class Number extends \Validate\Validation {

    public string $message = '{[?]} No es número';

    public function validate(\Validate\Validator $validator): bool {
        return is_numeric($validator->value);
    }
}
