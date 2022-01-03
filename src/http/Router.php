<?php

namespace Http;


class Router {
    private $urlParamId = ':';
    private $routesPath = root .  'src/routes/';
    private string $requestedUrl;
    private array $routes = [];

    public function __construct() {
        $this->requestedUrl = $this->sanitize($_SERVER['REQUEST_URI']);
        $this->requireRoutes();
    }
    private function requireRoutes() {
        $url = \Helpers\Tools::leftTrim('/', $this->requestedUrl);
        [$route] = explode('/', $url);

        $this->requestedUrl = \Helpers\Tools::leftTrim($route, '', $url);

        if (!is_dir($this->routesPath)) return;

        $route .= '.php';
        if (is_file($this->routesPath . $route))
            return require_once $this->routesPath . $route;

        $routes = scandir($this->routesPath);
        $route = strtolower($route);
        foreach ($routes as $files) {
            if ($route === strtolower($files))
                return require_once $this->routesPath . $files;
        }
    }
    private function setRoute(string $method, string $path, array $controllers) {
        $this->routes[$path][$method] = new \Http\Route($method, $path, $controllers);
    }
    /** @return \Http\Route[] */
    private function getRoutes() {
        $routes = $this->routes[$this->requestedUrl];
        $method = $_SERVER['REQUEST_METHOD'];
        return array_merge(
            $routes['ALL_REQUESTS'] ?? [],
            $routes[$method] ?? []
        );
    }
    /** @return callable[] */
    private function getControllers() {
        $routes = $this->getRoutes();
        $controllers = [];
        foreach ($routes as $route) {
            $controllers = array_merge($controllers, $route->controllers());
        }
        return $controllers;
    }
    public function use(string $route, callable ...$controllers) {
        $this->setRoute('ALL_REQUESTS', $route, $controllers);
    }
    public function post(string $route, callable ...$controllers) {
        $this->setRoute('POST', $route, $controllers);
    }
    public function get(string $route, callable ...$controllers) {
        $this->setRoute('GET', $route, $controllers);
    }
    public function delete(string $route, callable ...$controllers) {
        $this->setRoute('DELETE', $route, $controllers);
    }
    public function put(string $route, callable ...$controllers) {
        $this->setRoute('PUT', $route, $controllers);
    }
    private function sanitize(string $url) {
        $url = strtolower($url);
        $filteredUrl = filter_var($url, FILTER_SANITIZE_URL);
        return $filteredUrl;
    }
    private function getRequestParams() {
        // $route = $this->requestedUrl;
        // $splited_route = explode('/', $route);
        // $params_routes = array_filter(
        //     $this->routes,
        //     function (string $posible_route) use ($splited_route) {
        //         return (mb_strpos($posible_route, $this->urlParamId) !== false
        //             && count($splited_route) === count(explode('/', $posible_route)))
        //             || mb_strpos($posible_route, $this->lazyUrlMatch);
        //     },
        //     ARRAY_FILTER_USE_KEY
        // );
        // foreach ($params_routes as $_route => $_) {
        //     if (
        //         mb_strpos($_route, $this->lazyUrlMatch) !== false
        //         && \Helpers\Tools::startsWith(
        //             ([$lazy_route, $lazy_param] = explode($this->lazyUrlMatch, $_route))[0],
        //             $route
        //         )
        //     ) {
        //         $_REQUEST[$lazy_param] = \Helpers\Tools::leftTrim($lazy_route, $route);
        //         return $_route;
        //     }
        //     $_route = explode('/', $_route);
        //     foreach ($_route as $index => $string) {
        //         $is_param = substr($string, 0, 1) === $this->urlParamId;
        //         if (
        //             !$is_param
        //             && $string !== $splited_route[$index]
        //         ) break;

        //         if ($is_param) {
        //             $param = ltrim($string, $this->urlParamId);
        //             if ($splited_route[$index] !== '')
        //                 $_REQUEST[$param] = $splited_route[$index];
        //             $splited_route[$index] = $string;
        //         }
        //     }
        // }
        // return implode('/', $splited_route);
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
        $response = new \Http\Response(OK, null);
        $controllers = $this->getControllers();
        try {
            foreach ($controllers as $controller) {
                $exit = $controller($request, $response);
                if ($exit) break;
            }
        } catch (\Exception $e) {
            $response
                ->error($e->getMessage())
                ->status(INTERNAL_SERVER_ERROR);
        }
        return $response;
    }

    public function start(): \Http\Response {
        $params = $this->getRequestParams();
        $query = $this->getRequestQuery();
        $body = $this->getRequestBody();
        $request = new \Http\Request($params, $body, $query);
        return $this->getResponse($request);
    }
}
