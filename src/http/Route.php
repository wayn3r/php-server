<?php

namespace Http;

class Route {
    const ALL = 'ALL';
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

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
    private function matchMethod(string $method): bool {
        return $this->method === self::ALL || $this->method === $method;
    }
    private function matchPath(string $url): bool {
        return $this->path === self::ALL || $this->path === $url;
    }
    public function match(string $method, string $url): bool {
        return $this->matchMethod($method) && $this->matchPath($url);
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
