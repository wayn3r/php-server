<?php
// Cargando todos los archivos para procesar la petición
require_once  $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$app = \Http\Server::getServer();

$app->start();
