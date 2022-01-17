<?php

namespace Validate;

class Max extends \Validate\Validation {

    private string $min;

    public array $types = ['string', 'integer', 'double'];

    public function __construct(string $min) {
        $this->min = $min;
        $this->message = "{[?]} Debe ser menor o igual a [{$min}]";
    }

    public function validate(\Validate\Validator $validator): bool {
        return $validator->value <= $this->min;
    }
}
