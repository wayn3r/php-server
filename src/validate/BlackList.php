<?php

namespace Validate;

class BlackList extends \Validate\Validation {
    private array $blackList;
    public function __construct(array $blackList) {
        $this->blackList = $blackList;
    }
    public function validate(\Validate\Validator $validator): bool {
        return !in_array($validator->value, $this->blackList);
    }
}
