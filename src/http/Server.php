<?php

namespace Http;

class Server {
    private static \Http\Server $server;
    private array $routers;

    private function __construct() {
        $this->routers = [];
    }
    private function sanitize(string $url) {
        $url = strtolower($url);
        $filteredUrl = filter_var($url, FILTER_SANITIZE_URL);
        return $filteredUrl;
    }
    public static function getServer(): \Http\Server {
        return self::$server ??= new \Http\Server;
    }
    public function use(string $baseURL, \Http\Router $router): void {
        $this->routers[$baseURL] = $router;
    }

    public function start() {
        $url = $this->sanitize($_SERVER['REQUEST_URI']);
        if ($router = $this->getRouter($url)) {
            $router->start($url);
        }
    }

    public function getRouter(string &$url): ?\Http\Router {
        $splitedUrl = explode('/', $url);
        array_shift($splitedUrl);
        $baseURL = '';
        foreach ($splitedUrl as $partialUrl) {
            $baseURL .= '/' . $partialUrl;
            if (isset($this->routers[$baseURL])) {
                $url = \Helpers\Tools::leftTrim($baseURL, $url);
                return $this->routers[$baseURL];
            }
        }
        return null;
    }
}
