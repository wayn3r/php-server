<?php

namespace Core;

class HttpRequest {
    public array $params;
    public array $body;
    public array $query;

    function __construct(
        array $params = [],
        array $body = [],
        array $query = []
    ) {
        $this->params = $params;
        $this->body = $body;
        $this->query = $query;
    }
}
