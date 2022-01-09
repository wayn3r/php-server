<?php

namespace Validate;

abstract class Validation {

    /** 
     * @var string[] $types
     * Son los tipos de datos válidos para la validación
     */
    public array $types = [];
    /** 
     * @var bool $strict
     * Indica si la validación es obligatoria, este o no el valor
     */
    public bool $strict = false;
    /** 
     * @var bool $stop
     * Indica si continuará con las demás validaciones en caso de encontrar alguna falla
     */
    public bool $stop = false;
    /** 
     * @var string $message
     * Este es el mensaje de error que se muestra en caso de que la validación falle
     * 
     * {[?]} es el valor que se está validando
     * el texto que esta entre llaves se mostrará en caso de que exista un valor a validar.
     */
    public string $message = '{[?]} No es válido'; // 
    private array $regexs = [
        'time' => '/^(([01]{0,1}\d)|(2[0-3])):*[0-5]\d$/i',
        'phone' => '/^\d{10}$/i',
        'email' => '/^[\w\d._-]{3,}@[\w\d._-]{3,}\.\w{2,}$/i'
    ];
    public function config(array $config): self {
        foreach ($config as $atrribute => $value) {
            if (!property_exists($this, $atrribute)) continue;
            $property = new \ReflectionProperty($this, $atrribute);
            if ($property->isPrivate()) continue;
            $ptype = $property->getType()->getName();
            $vtype = gettype($value);
            if (
                !$property->hasType()
                || \Helpers\Strings::startsWith($ptype, $vtype)
                || ($vtype === 'double' && $ptype === 'float')
            )
                $this->$atrribute = $value;
        }
        return $this;
    }
    /** 
     * Contiene la lógica para validar el valor que se encuentre en el middleware
     * @return bool
     * Devuelve True si pasa la validación de lo contrario False
     */
    public abstract function validate(\Validate\Validator $validator): bool;
    public function regex(string $regex): string {
        if (isset($this->regexs[$regex]))
            return $this->regexs[$regex];
        throw new \Exception("[{$regex}] no esta definido, definalo si desea usarlo");
    }
}

class ValidationError {
    public string $prop;
    public string $message;
    public string $location;

    public function __construct(
        ?string $prop,
        string $message,
        string $location
    ) {
        $this->prop = $prop;
        $this->message = $message;
        $this->location = $location;
    }
}
