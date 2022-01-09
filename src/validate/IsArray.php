<?php

namespace Validate;

class IsArray extends \Validate\Validation {
    public bool $stop = true;
    public string $message = ' Debe ser una lista';
    public function validate(\Validate\Validator $validator): bool {
        return is_array($validator->value);
    }
}
