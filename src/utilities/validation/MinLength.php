<?php

namespace Utilities\Validation;

class MinLength extends \Utilities\Validation {
    private int $length;
    public array $types = ['string', 'integer', 'double', 'array'];
    public function __construct(int $length) {
        $this->length = $length;
        $this->message = "{[?]} Debe tener mínimo [{$length}] caracter(es)";
    }
    public function validate(\Utilities\Validator $validator): bool {
        return ((is_scalar($validator->value)
            && mb_strlen(trim($validator->value)) >= $this->length)
            || (is_array($validator->value)
                && count($validator->value) >= $this->length));
    }
}
