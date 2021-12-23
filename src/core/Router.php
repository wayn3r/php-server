<?php

namespace Core;

class Router {
    private static $noRequiredParamId = '@';
    private static $urlParamId = ':';
    private static $lazyUrlMatch = '@:';
    private static $routesPath = root . app . '/routes/';
    private static string $requestedUrl;
    private static array $get = [];
    private static array $post = [];
    private static array $put = [];
    private static array $delete = [];
    private static array $route = [];


    private static function filterUrl($url) {
        $url = strtolower($url);
        $filteredUrl = filter_var($url, FILTER_SANITIZE_URL);
        return $filteredUrl;
    }
    private static function get_current_method(): array {
        $current_request_method = [];
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $current_request_method = self::$get;
                break;
            case 'POST':
                $current_request_method = self::$post;
                break;
            case 'DELETE':
                $current_request_method = self::$delete;
                break;
            case 'PUT':
                $current_request_method = self::$put;
                break;
        }
        return $current_request_method;
    }
    private static function set_url_params(string $route, array $current_methods): string {
        if (isset($current_methods[$route])) return $route;
        $splited_route = explode('/', $route);
        $params_routes = array_filter(
            $current_methods,
            function (string $posible_route) use ($splited_route) {
                return (mb_strpos($posible_route, self::$urlParamId) !== false
                    && count($splited_route) === count(explode('/', $posible_route)))
                    || mb_strpos($posible_route, self::$lazyUrlMatch);
            },
            ARRAY_FILTER_USE_KEY
        );
        foreach ($params_routes as $_route => $_) {
            if (
                mb_strpos($_route, self::$lazyUrlMatch) !== false
                && \Helpers\Tools::startsWith(
                    ([$lazy_route, $lazy_param] = explode(self::$lazyUrlMatch, $_route))[0],
                    $route
                )
            ) {
                $_REQUEST[$lazy_param] = \Helpers\Tools::leftTrim($lazy_route, $route);
                return $_route;
            }
            $_route = explode('/', $_route);
            foreach ($_route as $index => $string) {
                $is_param = substr($string, 0, 1) === self::$urlParamId;
                if (
                    !$is_param
                    && $string !== $splited_route[$index]
                ) break;

                if ($is_param) {
                    $param = ltrim($string, self::$urlParamId);
                    if ($splited_route[$index] !== '')
                        $_REQUEST[$param] = $splited_route[$index];
                    $splited_route[$index] = $string;
                }
            }
        }
        return implode('/', $splited_route);
    }
    private static function get_request_params() {
        $data = $_REQUEST;
        $params = []; //Inicializando params como array vacio

        // Guardando datos ordenados y con el tipo de dato especificado
        foreach (self::$route['params'] as $param => $type) {
            $required = substr($param, 0, 1) !== self::$noRequiredParamId;
            if ($required && !isset($data[$param])) return "Parametros requeridos no fueron suministrados, el campo [{$param}] es requerido";


            $param = ltrim($param, self::$noRequiredParamId);

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

    public static function post(string $route, string $controller, array $params = [], callable ...$middlewares) {
        $route = strtolower($route);
        self::$post[$route] = ['controller' => $controller, 'params' => $params, 'middlewares' => $middlewares];
    }
    public static function get(string $route, string $controller, array $params = [], callable ...$middlewares) {
        $route = strtolower($route);
        self::$get[$route] = ['controller' => $controller, 'params' => $params, 'middlewares' => $middlewares];
    }
    public static function delete(string $route, string $controller, array $params = [], callable ...$middlewares) {
        $route = strtolower($route);
        self::$delete[$route] = ['controller' => $controller, 'params' => $params, 'middlewares' => $middlewares];
    }
    public static function put(string $route, string $controller, array $params = [], callable ...$middlewares) {
        $route = strtolower($route);
        self::$put[$route] = ['controller' => $controller, 'params' => $params, 'middlewares' => $middlewares];
    }
    /**
     * Busca el route asociado a la petición
     * @param string $route
     * La ruta que se encuentra en la petición
     * @return string 
     * El archivo de la carpeta Routes que coincida con $route
     * devuelve un string vacío en caso de no encontrarlo
     */
    public static function getRouteFile() {
        $url = \Helpers\Tools::leftTrim('/', $_SERVER['REQUEST_URI']);
        [$route] = explode('/', $url);

        self::$requestedUrl = \Helpers\Tools::leftTrim($route, '', $url);

        if (!is_dir(self::$routesPath)) return;

        $route .= '.php';
        if (is_file(self::$routesPath . $route))
            return require_once self::$routesPath . $route;

        $routes = scandir(self::$routesPath);
        $route = strtolower($route);
        foreach ($routes as $files) {
            if ($route === strtolower($files))
                return require_once self::$routesPath . $files;
        }
    }

    public static function route(): \Core\HttpResponse {
       
        // obteniedo la ruta y el archivo correspondiente
        self::getRouteFile();
        //Inicializando el result como un HttpResponse con datos genericos
        $route = self::filterUrl(self::$requestedUrl);
        // Buscando las rutas guardadas dependiendo el tipo de metodo http utilizado en la peticicion
        $current_request_method = self::get_current_method();
        // Verificando si hay parametros pasados por la url, de ser asi se pondran en la variable $_REQUEST
        $route = self::set_url_params($route, $current_request_method);
        // Verificando que la ruta solicitada exista
        if (!isset($current_request_method[$route]))
            return new \Core\HttpResponse(NOT_FOUND, 'La ruta solicitada no fue encontrada');

        self::$route = $current_request_method[$route];
        // Obteniendo los paramentros correctamente formateados
        $params_result = self::get_request_params(); // <-- Este metodo utiliza la propiedad self::$route por eso se asigna su valor antes de llamarlo
        // Verificando que los paramentros esten correctos
        if (!is_array($params_result)) return new \Core\HttpResponse(BAD_REQUEST, $params_result);

        // dividiendo los parametros suministrados por el usuario entre los requeridos por el controlador y los que no
        [$params, $body] = $params_result;

        [$controller, $method] = explode(self::$noRequiredParamId, self::$route['controller']);

        $controller = '\\Controller\\' . $controller;
        // Verificando que exista la clase antes de instanciarla
        if (!class_exists($controller))
            return new \Core\HttpResponse(INTERNAL_SERVER_ERROR, 'El controlador asociado a esta ruta no esta definido');

        
        // creando el request del usuario
        $request = new \Core\HttpRequest($params, $body);
        // creando el response que se enviará al usuario
        $response = new \Core\HttpResponse(OK, null);

        try {
            foreach (self::$route['middlewares'] as $middleware) {
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
