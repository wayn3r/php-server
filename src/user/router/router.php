<?php

namespace User\Router;

$router = new \Http\Router;
$validator = new \Utilities\Validator;
$router->get(
    '/:token',
    function (\Http\Request $req, \Http\Response $res) {
        $res->status(BAD_REQUEST)->json($req->params());
    }
);

return $router;
