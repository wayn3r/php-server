<?php

namespace Utilities\Validation;

class NotExists extends \Utilities\Validation {
    public array $types = ['string', 'integer', 'double',];
    public string $message = '{[?]} Ya existe';
    public string $target;

    private \Core\Model $model;
    public function __construct(\Core\Model $model) {
        $this->model = $model;
    }
    public function validate(\Utilities\Validator $validator): bool {
        return !$this->model->find($this->target ?? $validator->prop, $validator->value);
    }
}
