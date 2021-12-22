<?php

namespace Utilities\Validation;

class BlackList extends \Utilities\Validation {
    private array $blackList;
    public function __construct(array $blackList) {
        $this->blackList = $blackList;
    }
    public function validate(\Utilities\Validator $validator): bool {
        return !in_array($validator->value, $this->blackList);
    }
}
