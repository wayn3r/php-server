<?php

namespace Http;

class Request {
    private array $params;
    private array $body;
    private array $query;

    function __construct(
        array $params,
        array $body,
        array $query
    ) {
        $this->params = $params;
        $this->body = $body;
        $this->query = $query;
    }
    public function params() {
        return $this->params;
    }
    public function body() {
        return $this->body;
    }
    public function query() {
        return $this->query;
    }
}
