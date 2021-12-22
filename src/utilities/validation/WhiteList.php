<?php

namespace Utilities\Validation;

class WhiteList extends \Utilities\Validation {
    private array $whiteList;
    public function __construct(array $whiteList) {
        $this->whiteList = $whiteList;
    }
    public function validate(\Utilities\Validator $validator): bool {
        return in_array($validator->value, $this->whiteList);
    }
}
