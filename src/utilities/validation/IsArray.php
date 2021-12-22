<?php

namespace Utilities\Validation;

class IsArray extends \Utilities\Validation {
    public bool $stop = true;
    public string $message = ' Debe ser una lista';
    public function validate(\Utilities\Validator $validator): bool {
        return is_array($validator->value);
    }
}
