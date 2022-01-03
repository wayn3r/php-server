<?php

namespace Http;

class Server {
    private static \Http\Server $server;
    private array $routers;

    private function __construct() {
        $this->routers = [];
    }
    public static function init(): \Http\Server {
        return self::$server ??= new \Http\Server;
    }
    public function use(string $baseURL, callable $getRouter): void {
        $router = $getRouter();
        if ($router instanceof \Http\Router) {
            $this->routers[$baseURL] = $router;
        }
    }

    public function start() {
        $url = $_SERVER['REQUEST_URI'];
        print_r($_SERVER);
    }

    public function getRouter(string $baseURL): ?\Http\Router {
        $splited_url = explode('/', $baseURL);
        return $this->routers[$baseURL] ?? null;
    }
}
