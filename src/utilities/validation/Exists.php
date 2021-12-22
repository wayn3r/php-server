<?php

namespace Utilities\Validation;

class Exists extends \Utilities\Validation {
    public array $types = ['string', 'integer', 'double',];
    public string $message = '{[?]} No existe';
    public string $target;
    public array $where = [];

    private \Core\Model $model;
    public function __construct(\Core\Model $model) {
        $this->model = $model;
    }
    public function validate(\Utilities\Validator $validator): bool {
        return $this->model->findByFields(array_merge(
            [$this->target ?? $validator->prop => $validator->value],
            $this->where,
        ));
    }
}
