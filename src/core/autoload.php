<?php
require_once 'config.php';

$composerAutoloader = root . '/vendor/autoload.php';
if (is_file($composerAutoloader))
    require_once  $composerAutoloader;

// autoload por namespaces
spl_autoload_register(function ($className) {
    $root = root . app . '/';
    $path = explode('\\', $className);
    $filename = array_pop($path) . '.php';
    $path = implode('/', $path) . '/';

    $file = $root . $path . $filename;
    if (!file_exists($file))
        $file = $root . strtolower($path) . $filename;

    if (file_exists($file))
        require_once $file;
});


// obteniendo los datos del cuerpo de la peticion
$request = json_decode(file_get_contents("php://input"), true);
if (is_array($request))
    foreach ($request as $key => $content) {
        $_REQUEST[$key] = $content;
    }
