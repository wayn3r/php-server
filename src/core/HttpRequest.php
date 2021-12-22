<?php

namespace Core;

class HttpRequest {
    public array $params;
    public array $body;
    function __construct(array $params = [], array $body = []) {
        $this->params = $params;
        $this->body = $body;
    }
}
