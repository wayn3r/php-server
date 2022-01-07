<?php

namespace User\Router;

$router = new \Http\Router;
$validator = new \Utilities\Validator;
$router->get(
    '/klk/:token',
    $validator->number()->param('token'),
    $validator->checkout(),
    function (\Http\Request $req, \Http\Response $res) {
        $res->status(BAD_REQUEST)->json($req->params());
    }
);

return $router;
