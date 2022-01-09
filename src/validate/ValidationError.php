<?php 

namespace Validate;

final class ValidationError {

    public string $parameter;
    public string $message;
    public ?string $typeError;
    public ?string $location;
    
    public function __construct(
        string $parameter, 
        string $message,
        ?string $typeError, 
        ?string $location
    ){
        $this->parameter = $parameter;
        $this->message = $message;
        if($typeError)
            $this->typeError = $typeError;
        if($location)
            $this->location = $location;
    }
}