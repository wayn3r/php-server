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
require_once  'core/autoload.php';

// recogiendo cualquier mensaje escrito en el cuerpo de la pagina
ob_start();

// Procesando la peticion 
$response = \Core\Router::route();

$response->warning = preg_replace('/(\\n)|(<[^>]*>)/i', '', ob_get_contents());
// eliminando cualquier mensaje escrito en el cuerpo de la pagina
ob_clean();

// devolviendo la respuesta
echo $response;