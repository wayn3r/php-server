<?php

/*******************************
 * CONFIGURACIÓN DEL PROYECTO  
 *******************************/
setlocale(
    LC_ALL,
    'Spanish_Dominican_Republic',
    'Spanish_Spain',
    'es_ES',
    'Spanish',
    'es_ES@euro',
    'es_ES',
    'esp'
);

// Cargando todos los archivos para procesar la petición
require_once  $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// configuracion del header, más que nada para petición de CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");


// Procesando la peticion y devolviendo la respuesta
$app = \Http\Server::init();

// $app->use('/user', fn () => \App\Router::user());

$app->start();
