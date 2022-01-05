<?php

namespace Http;

use function PHPSTORM_META\type;

class Router {
    private const URL_PARAM_ID = ':';
    private string $requestedUrl;
    /** @var \Http\Route[]  */
    private array $routes = [];

    private function setRoute(string $method, string $path, array $controllers) {
        $this->routes[] = new \Http\Route($method, $path, $controllers);
    }

    /** @return callable[] */
    private function getControllers() {
        $controllers = [];
        $method = $_SERVER['REQUEST_METHOD'];
        foreach ($this->routes as $route) {
            if ($route->match($method, $this->requestedUrl)) {
                $controllers = array_merge($controllers, $route->controllers());
            }
        }
        return $controllers;
    }
    public function use($path, callable ...$controllers) {
        if (is_callable($path)) {
            $controllers = [$path, ...$controllers];
            $path = \Http\Route::ALL;
        }
        $this->setRoute(\Http\Route::ALL, $path, $controllers);
    }
    public function post(string $path, callable ...$controllers) {
        $this->setRoute(\Http\Route::POST, $path, $controllers);
    }
    public function get(string $path, callable ...$controllers) {
        $this->setRoute(\Http\Route::GET, $path, $controllers);
    }
    public function delete(string $path, callable ...$controllers) {
        $this->setRoute(\Http\Route::DELETE, $path, $controllers);
    }
    public function put(string $path, callable ...$controllers) {
        $this->setRoute(\Http\Route::PUT, $path, $controllers);
    }

    private function getRequestParams() {
        return [];
    }
    private function getRequestBody() {
        // obteniendo los datos del cuerpo de la peticion
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        return array_merge($body, $_POST);
    }
    private function getRequestQuery() {
        return $_GET;
    }
    private function getResponse(\Http\Request $request): \Http\Response {
        $response = new \Http\Response;
        $controllers = $this->getControllers();
        try {
            foreach ($controllers as $controller) {
                $exit = $controller($request, $response);
                if ($exit) break;
            }
        } catch (\Exception $e) {
            $response
                ->status(INTERNAL_SERVER_ERROR)
                ->send($e->getMessage());
        }
        return $response;
    }

    public function start(string $url): \Http\Response {
        $this->requestedUrl = $url;
        $params = $this->getRequestParams();
        $query = $this->getRequestQuery();
        $body = $this->getRequestBody();
        $request = new \Http\Request($params, $body, $query);
        return $this->getResponse($request);
    }
}
