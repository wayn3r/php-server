<?php

namespace Core;

class Middleware extends \Utilities\Validator {
    /**
     * Este metodo hace uso del metodo validate de los modelos y devuelve los errores
     * @param string $param
     * El nombre del parametro del modelo a validar de la petición
     * @param string $strict
     * Determinará si la actualización se hara de forma estricta o no
     * @return callable
     * Devuelve una función que es la encargada de validar la petición
     */
    public static function validarModelo(string $param, bool $strict = false): callable {
        return function (\Core\HttpRequest $request) use ($param, $strict) {
            /** @var \Core\Model $model */
            $model = @$request->params[$param];
            // verificar los errores en el modelo
            if ($errors = $model->validate($strict))
                return new \Core\HttpResponse(BAD_REQUEST, $errors);
        };
    }

    public function param(string $param): callable {
        $validators = $this->validators;
        $this->clearValidators();
        return function (\Core\HttpRequest $request) use ($param, $validators) {
            $this->validators = $validators;
            $this->check($request->params, $param, false);
        };
    }
    public function body(string $param): callable {
        $validators = $this->validators;
        $this->clearValidators();
        return function (\Core\HttpRequest $request) use ($param, $validators) {
            $this->validators = $validators;
            $this->check($request->body, $param, false);
        };
    }

    public function checkout() {
        return function () {
            if ($errors = $this->errors())
                return new \Core\HttpResponse(BAD_REQUEST, $errors);
        };
    }
}
