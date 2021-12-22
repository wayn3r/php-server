<?php

namespace Utilities\Validation;

class NotEmpty extends \Utilities\Validation {
    public bool $stop = true;
    public string $message = ' No puede estar vacio';
    public function validate(\Utilities\Validator $validator): bool {
        return !empty(is_string($validator->value)
            ? trim($validator->value)
            : $validator->value);
    }
}
