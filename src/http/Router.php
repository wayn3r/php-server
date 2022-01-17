<?php

namespace Http;

class Router {

    /** @var \Http\Route[]  */
    private array $routes = [];

    private function setRoute(string $method, string $path, array $controllers) {
        $this->routes[] = new \Http\Route($method, $path, $controllers);
    }

    /** @return callable[] */
    private function getControllers(\Http\Request $request): array {
        $controllers = [];
        $method = $request->method();
        $url = $request->url();

        foreach ($this->routes as $route) {
            if ($route->match($method, $url)) {
                $request->getParamsFromRoute($route);
                $request->trimUrl($route->path());
                $controllers = array_merge($controllers, $route->controllers());
            }
        }

        return $controllers;
    }

    protected function getNextController(
        array $controllers,
        \Http\Request $request,
        \Http\Response $response
    ) {
        return function (...$args) use ($controllers, $request, $response) {
            $controller = current($controllers);
            if (!$controller) {
                return;
            }

            next($controllers);
            $args = [
                ...$args,
                $request,
                $response,
                $this->getNextController($controllers, $request, $response),
            ];
            $controller(...$args);
        };
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

    public function __invoke(
        \Http\Request $request,
        \Http\Response $response,
        ?callable $next = null
    ): ?\Http\Response {
        $controllers = $this->getControllers($request);
        if (!$controllers) {
            return ($next) ? $next() : null;
        }

        try {
            $this->getNextController($controllers, $request, $response)();
        } catch (\Exception $e) {
            $response
                ->status(500)
                ->send($e->getMessage());
        }

        return $response;
    }
}
