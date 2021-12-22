<?php

namespace Utilities\Validation;

class Min extends \Utilities\Validation {
    private string $min;
    public array $types = ['string', 'integer', 'double'];
    public function __construct(string $min) {
        $this->min = $min;
        $this->message = "{[?]} Debe ser mayor o igual a [{$min}]";
    }
    public function validate(\Utilities\Validator $validator): bool {
        return $validator->value >= $this->min;
    }
}
