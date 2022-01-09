<?php

namespace Http;

final class Request {
    private array $params;
    private array $body;
    private array $query;
    private string $fullUrl;
    private string $url;
    private string $method;

    private const QUERY_START_STRING = '?';

    public function __construct(array $body, array $query) {
        $this->params = [];
        $this->body = $body;
        $this->query = $query;
        $this->fullUrl = $this->getUrl();
        $this->url = $this->fullUrl;
        $this->method = $_SERVER['REQUEST_METHOD'];
    }
    private function getUrl():string {
        $fileRelativeRoot = rtrim(dirname($_SERVER['PHP_SELF']), '/');
        $url = \Helpers\Strings::leftTrim($fileRelativeRoot, $_SERVER['REQUEST_URI']);
        return $this->sanitize($url);
    }
    private function sanitize(string $url) {
        $url = strtolower($url);
        $filteredUrl = filter_var($url, FILTER_SANITIZE_URL);
        return explode(self::QUERY_START_STRING, $filteredUrl)[0];
    }
    public function params() {
        return $this->params;
    }
    public function getParamsFromRoute(\Http\Route $route) {
        $this->params = array_merge($this->params, $route->getParamsFromUrl($this->url));
    }
    public function body() {
        return $this->body;
    }
    public function query() {
        return $this->query;
    }
    public function url() {
        return $this->url;
    }
    public function method(): string {
        return $this->method;
    }
    public function fullUrl() {
        return $this->fullUrl;
    }
    public function trimUrl(string $partialUrl) {
        return $this->url = preg_replace("/^{$partialUrl}/", '', $this->url);
    }
}
