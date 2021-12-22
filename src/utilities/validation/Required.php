<?php

namespace Utilities\Validation;

class Required extends \Utilities\Validation {
    public bool $strict = true;
    public bool $stop = true;
    public string $message = ' Es requerido';
    public function validate(\Utilities\Validator $validator): bool {
        return isset($validator->value);
    }
}
