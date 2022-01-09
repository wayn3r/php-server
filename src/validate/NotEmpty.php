<?php

namespace Validate;

class NotEmpty extends \Validate\Validation {
    public bool $stop = true;
    public string $message = ' No puede estar vacio';
    public function validate(\Validate\Validator $validator): bool {
        return !empty(is_string($validator->value)
            ? trim($validator->value)
            : $validator->value);
    }
}
