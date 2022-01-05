<?php

namespace Http;

class Server {
    private static \Http\Server $server;
    private array $routers;
    private const QUERY_START_STRING = '?';

    private function __construct() {
        $this->routers = [];
    }
    private function sanitize(string $url) {
        $url = strtolower($url);
        $filteredUrl = filter_var($url, FILTER_SANITIZE_URL);
        return explode(self::QUERY_START_STRING, $filteredUrl)[0];
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
            ob_start();
            $router->start($url);
            ob_end_flush();
        }
    }

    public function getRouter(string &$requestUrl): ?\Http\Router {
        foreach ($this->routers as $url => $router) {
            if (strpos($requestUrl, $url) === 0) {
                $requestUrl = substr($requestUrl, strlen($url));
                return $router;
            }
        }
        return null;
    }
}
