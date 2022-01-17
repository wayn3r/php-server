<?php

namespace Validate;

class WhiteList extends \Validate\Validation {

    private array $whiteList;

    public function __construct(array $whiteList) {
        $this->whiteList = $whiteList;
    }

    public function validate(\Validate\Validator $validator): bool {
        return in_array($validator->value, $this->whiteList);
    }
}
