<?php

namespace Validate;

class MaxLength extends \Validate\Validation {

    public int $length;

    public array $types = ['string', 'integer', 'double', 'array'];

    public function __construct(int $length) {
        $this->length = $length;
        $this->message = "{[?]} Debe tener máximo [{$length}] caracter(es)";
    }

    public function validate(\Validate\Validator $validator): bool {
        return (is_scalar($validator->value)
            && mb_strlen(trim($validator->value)) <= $this->length)
            || (is_array($validator->value)
                && count($validator->value) <= $this->length);
    }
}
