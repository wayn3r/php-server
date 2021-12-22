<?php

/*******************************
 * CONFIGURACIÓN DEL PROYECTO  
 *******************************/

date_default_timezone_set('America/Santo_Domingo');
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
// configuracion para que se muestren los errores de php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// configuracion del header, más que nada para petición de CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Cargando todos los archivos para procesar la petición
require_once 'Core/Loader.php';

// Procesando la peticion y devolviendo la respuesta
echo \Core\Router::route();