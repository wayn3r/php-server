<?php
// Cargando todos los archivos para procesar la petición
require_once  $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
$userRouter = require_once('user/router/router.php');
// Procesando la peticion y devolviendo la respuesta
$app = \Http\Server::getServer();

$app->use('/user/:id/auth/:type', $userRouter);

$app->start();
