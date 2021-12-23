<?php

namespace Core;

class Router {
    private $urlParamId = ':';
    private $lazyUrlMatch = '@:';
    private $routesPath = root .  'src/routes/';
    private string $requestedUrl;
    private array $routes = [];
    private array $controllers = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    public function __construct() {
        $this->requestedUrl = $_SERVER['REQUEST_URI'];
        $controllers = $this->controllers[$_SERVER['REQUEST_METHOD']] ?? [];
        $this->routes = array_merge($this->routes, $controllers);
    }
    private function filterUrl($url) {
        $url = strtolower($url);
        $filteredUrl = filter_var($url, FILTER_SANITIZE_URL);
        return $filteredUrl;
    }
    private function setUrlParams(string $route, array $current_methods): string {
        if (isset($current_methods[$route])) return $route;
        $splited_route = explode('/', $route);
        $params_routes = array_filter(
            $current_methods,
            function (string $posible_route) use ($splited_route) {
                return (mb_strpos($posible_route, $this->urlParamId) !== false
                    && count($splited_route) === count(explode('/', $posible_route)))
                    || mb_strpos($posible_route, $this->lazyUrlMatch);
            },
            ARRAY_FILTER_USE_KEY
        );
        foreach ($params_routes as $_route => $_) {
            if (
                mb_strpos($_route, $this->lazyUrlMatch) !== false
                && \Helpers\Tools::startsWith(
                    ([$lazy_route, $lazy_param] = explode($this->lazyUrlMatch, $_route))[0],
                    $route
                )
            ) {
                $_REQUEST[$lazy_param] = \Helpers\Tools::leftTrim($lazy_route, $route);
                return $_route;
            }
            $_route = explode('/', $_route);
            foreach ($_route as $index => $string) {
                $is_param = substr($string, 0, 1) === $this->urlParamId;
                if (
                    !$is_param
                    && $string !== $splited_route[$index]
                ) break;

                if ($is_param) {
                    $param = ltrim($string, $this->urlParamId);
                    if ($splited_route[$index] !== '')
                        $_REQUEST[$param] = $splited_route[$index];
                    $splited_route[$index] = $string;
                }
            }
        }
        return implode('/', $splited_route);
    }
    private function getRequestBody(\Core\HttpRequest $request) {
        // obteniendo los datos del cuerpo de la peticion
        $body = json_decode(file_get_contents("php://input"), true);
        if (is_array($body))
            foreach ($body as $key => $content) {
                $request->body[$key] = $content;
            }
    }
    private function getRequestParams() {
        $data = $_REQUEST;
        $params = []; //Inicializando params como array vacio

        // Guardando datos ordenados y con el tipo de dato especificado
        foreach ($this->route['params'] as $param => $type) {
            $required = substr($param, 0, 1) !== $this->noRequiredParamId;
            if ($required && !isset($data[$param])) return "Parametros requeridos no fueron suministrados, el campo [{$param}] es requerido";


            $param = ltrim($param, $this->noRequiredParamId);

            if (isset($data[$param])) {
                if (
                    substr($type, 0, 4) === 'new '
                    || class_exists($type)
                ) {
                    $type = ltrim($type, 'new ');
                    $model = '\\Model\\' . $type;

                    if (class_exists($type)) {
                        try {
                            $data[$param] = new $type($data[$param]);
                        } catch (\Exception $e) {
                            return "El parametro [{$param}] tiene un formato invalido.";
                        }
                    } else if (class_exists($model)) {
                        $tipo_dato = gettype($data[$param]);
                        if (!is_array($data[$param]))
                            return "El parametro [{$param}] es un objeto, [{$tipo_dato}] recibido";
                        $data[$param] = new $model($data[$param]);
                    } else return "Se ha especificado un tipo de dato no definido: [{$type}]";

                    $type = 'object';
                }
                if ($type === 'integer' || $type === 'double') {
                    $data[$param] = floatval($data[$param]);
                    $type = 'double';
                }

                if (gettype($data[$param]) === $type) {
                    $params[$param] = $data[$param];
                    unset($data[$param]);
                }
            }
        }
        return [$params, $data];
    }
    private function setController(
        string $method,
        string $route,
        array $controllers
    ) {
        $route = strtolower($route);
        $this->controllers[$method][$route] = $controllers;
    }
    public function use(string $route, callable ...$controllers) {
        $route = strtolower($route);
        $this->routes[$route] = $controllers;
    }
    public function post(string $route, callable ...$controllers) {
        $this->setController('POST', $route, $controllers);
    }
    public function get(string $route, callable ...$controllers) {
        $this->setController('GET', $route, $controllers);
    }
    public function delete(string $route, callable ...$controllers) {
        $this->setController('DELETE', $route, $controllers);
    }
    public function put(string $route, callable ...$controllers) {
        $this->setController('PUT', $route, $controllers);
    }


    /**
     * Busca el route asociado a la petición
     * @param string $route
     * La ruta que se encuentra en la petición
     * @return string 
     * El archivo de la carpeta Routes que coincida con $route
     * devuelve un string vacío en caso de no encontrarlo
     */
    public function getRouteFile() {
        $url = \Helpers\Tools::leftTrim('/', $_SERVER['REQUEST_URI']);
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

    public function start(): \Core\HttpResponse {
        $request = new \Core\HttpRequest;
        // obteniedo la ruta y el archivo correspondiente
        $this->getRouteFile();
        //Inicializando el result como un HttpResponse con datos genericos
        $route = $this->filterUrl($this->requestedUrl);
        // Buscando las rutas guardadas dependiendo el tipo de metodo http utilizado en la peticicion
        // Verificando si hay parametros pasados por la url, de ser asi se pondran en la variable $_REQUEST
        $route = $this->setUrlParams($request);
        // Verificando que la ruta solicitada exista
        if (!isset($current_request_method[$route]))
            return new \Core\HttpResponse(NOT_FOUND, 'La ruta solicitada no fue encontrada');
        $this->getRequestBody($request);
        $this->route = $current_request_method[$route];
        // Obteniendo los paramentros correctamente formateados
        $params_result = $this->getRequestParams(); // <-- Este metodo utiliza la propiedad $this->route por eso se asigna su valor antes de llamarlo
        // Verificando que los paramentros esten correctos
        if (!is_array($params_result)) return new \Core\HttpResponse(BAD_REQUEST, $params_result);

        // dividiendo los parametros suministrados por el usuario entre los requeridos por el controlador y los que no
        [$params, $body] = $params_result;

        [$controller, $method] = explode($this->noRequiredParamId, $this->route['controller']);

        $controller = '\\Controller\\' . $controller;
        // Verificando que exista la clase antes de instanciarla
        if (!class_exists($controller))
            return new \Core\HttpResponse(INTERNAL_SERVER_ERROR, 'El controlador asociado a esta ruta no esta definido');

        
        // creando el request del usuario
        $request = new \Core\HttpRequest($params, $body);
        // creando el response que se enviará al usuario
        $response = new \Core\HttpResponse(OK, null);

        try {
            foreach ($this->route['middlewares'] as $middleware) {
                $response->response = $middleware($request);
                if ($response->response) break;
            }
            if (!$response->response) {
                $controller = new $controller;
                $controller->request = $request;
                $response->response = call_user_func_array(
                    [$controller, $method], //llamando el controlador y metodo especificados en la ruta
                    $request->params //pasando los parametros con cualquier modificacion de los middlwares
                );
            }
            // forzando el resultado a un HttpResponse
            if (
                gettype($response->response) === 'object'
                && get_class($response->response) === \Core\HttpResponse::class
            ) $response = \Core\HttpResponse::cast($response->response);
        } catch (\Exception $e) {
            $response->error = $e->getMessage();
        }
       
        // Si existe algun error se devuelve
        if ($response->error) $response->status = INTERNAL_SERVER_ERROR;

        return $response;
    }
}
