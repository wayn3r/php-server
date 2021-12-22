<?php
require_once 'Core/Globals.php';

spl_autoload_register(function ($className) {
    $path = explode('\\', $className);
    $filename = array_pop($path) . '.php';
    $path = implode('/', $path) . '/';

    if (!file_exists($path . $filename))
        $path = strtolower($path);

    if (file_exists($path . $filename))
        require_once $path . $filename;
});

// obteniendo los datos del cuerpo de la peticion
$request = json_decode(file_get_contents("php://input"), true);
if (is_array($request))
    foreach ($request as $key => $content) {
        $_REQUEST[$key] = $content;
    }

// importando la ruta solicitada
$ruta = isset($_GET[route]) ? $_GET[route] : '';
$ruta = explode("/", $ruta)[0];
$import = \Core\Router::getRoute($ruta);

if (file_exists($import)) {
    require_once $import;
    $_GET[route] = \Helpers\Tools::leftTrim($ruta, $_GET[route]);
}
