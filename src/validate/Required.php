<?php

namespace Validate;

class Required extends \Validate\Validation {

    public bool $strict = true;

    public bool $stop = true;

    public string $message = ' Es requerido';

    public function validate(\Validate\Validator $validator): bool {
        return isset($validator->value);
    }
}
