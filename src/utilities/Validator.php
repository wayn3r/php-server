<?php

namespace Utilities;

class Validator {
    public $value;
    public string $prop;
    public bool $clearValidatorsAfterCheck = true;
    private array $errors = [];
    protected array $validators = [];

    private function validate(string $prop) {
        $cast = function (\Utilities\Validation $validation) {
            return $validation;
        };
        foreach ($this->validators as $validation) {
            $validation =  $cast($validation);
            $type_error = '';
            if (
                isset($this->value)
                && count($validation->types) !== 0
                && !in_array(
                    $type = gettype($this->value),
                    $validation->types
                )
            )
                $type_error = '. Tipo de dato incorrecto, se esperaba [' . implode(', ', $validation->types) . "] y se recibió [{$type}]";

            $must_validate = $validation->strict || isset($this->value);
            if (
                empty($type_error)
                && ($must_validate && $validation->validate($this)
                    || !$must_validate)
            ) continue;

            $message = preg_replace_callback(
                '/{.*\?.*}/i',
                function (array $matches) {
                    $match = $matches[0];
                    if (
                        !isset($this->value)
                        || empty($this->value)
                        || !is_scalar($this->value)
                    )
                        return '';
                    $match = str_replace(['{', '}'], '', $match);
                    return str_replace('?', $this->value, $match);
                },
                $validation->message
            );

            $this->errors[] = "[{$prop}]" . $message . $type_error;
            if ($validation->stop) break;
        }
    }

    public function check($object, string $prop, bool $self = true) {
        $this->prop = $prop;
        if ($self) $this->value = $object;
        else if (isset($object->$prop))
            $this->value = $object->$prop;
        else if (is_array($object) && isset($object[$prop]))
            $this->value = $object[$prop];
        // Agrega al arreglo de errors los errores que encuentre en el value
        $this->validate($prop);
        if ($this->clearValidatorsAfterCheck)
            $this->clearValidators();
        unset($this->value);
        unset($this->prop);
        return $this;
    }
    public function add(\Utilities\Validation $validation) {
        $this->validators[get_class($validation)] = $validation;
        return $this;
    }
    public function clearValidators(): self {
        $this->validators = [];
        return $this;
    }
    /**
     * Devuelve los errores que esten en el Middleware y elimina los errores y los metodos que se han definido en las validaciones
     * @return array
     */
    public function errors(): array {
        $this->clearValidators();
        $errors = $this->errors;
        $this->errors = [];
        return $errors;
    }
    public function required(array $config = []) {
        return $this->add((new \Utilities\Validation\Required)->config($config));
    }
    public function notEmpty(array $config = []) {
        return $this->add((new \Utilities\Validation\NotEmpty)->config($config));
    }
    public function email(array $config = []) {
        return $this->add((new \Utilities\Validation\Email)->config($config));
    }
    public function phone(array $config = []) {
        return $this->add((new \Utilities\Validation\Phone)->config($config));
    }
    public function date(array $config = []) {
        return $this->add((new \Utilities\Validation\Date)->config($config));
    }
    public function time(array $config = []) {
        return $this->add((new \Utilities\Validation\Time)->config($config));
    }
    public function datetime(array $config = []) {
        return $this->add((new \Utilities\Validation\DateTime)->config($config));
    }
    public function number(array $config = []) {
        return $this->add((new \Utilities\Validation\Number)->config($config));
    }
    public function minLength(int $length, array $config = []) {
        return $this->add((new \Utilities\Validation\MinLength($length))->config($config));
    }
    public function maxLength(int $length, array $config = []) {
        return $this->add((new \Utilities\Validation\MaxLength($length))->config($config));
    }
    public function whitelist(array $whiteList, array $config = []) {
        return $this->add((new \Utilities\Validation\whitelist($whiteList))->config($config));
    }
    public function blacklist(array $blackList, array $config = []) {
        return $this->add((new \Utilities\Validation\BlackList($blackList))->config($config));
    }
    public function password(array $config = []) {
        return $this->add((new \Utilities\Validation\Password)->config($config));
    }
    public function array(array $config = []) {
        return $this->add((new \Utilities\Validation\IsArray)->config($config));
    }
    public function min(string $min, array $config = []) {
        return $this->add((new \Utilities\Validation\Min($min))->config($config));
    }
    public function max(string $max, array $config = []) {
        return $this->add((new \Utilities\Validation\Max($max))->config($config));
    }

    public function param(string $param): callable {
        $validators = $this->validators;
        $this->clearValidators();
        return function (\Http\Request $request, $_, callable $next) use ($param, $validators) {
            $this->validators = $validators;
            $this->check($request->params(), $param, false);
            $next();
        };
    }
    public function body(string $param): callable {
        $validators = $this->validators;
        $this->clearValidators();
        return function (\Http\Request $request, $_, callable $next) use ($param, $validators) {
            $this->validators = $validators;
            $this->check($request->body(), $param, false);
            $next();
        };
    }

    public function checkout() {
        return function ($_, \Http\Response $res, callable $next) {
            if ($errors = $this->errors())
                return $res->status(BAD_REQUEST)->json($errors);
            $next();
        };
    }
}
