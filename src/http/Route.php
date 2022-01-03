<?php

namespace Http;

class Route {
    private string $method;
    private string $path;
    /** @var callable[] $controllers */
    private array $controllers;

    function __construct(
        string $method,
        string $path,
        array $controllers
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->controllers = $controllers;
    }

    public function method(): string {
        return $this->method;
    }

    public function path(): string {
        return $this->path;
    }
    /** @return callable[] */
    public function controllers(): array {
        return $this->controllers;
    }
}
