<?php

namespace User\Router;

use \Http\Router;

$router = new Router;

$router->use('/auth', function () {
    echo 'adios';
});
$router->get('/auth', function () {
    echo 'hola';
});

return $router;
