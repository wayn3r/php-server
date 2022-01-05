<?php

namespace User\Router;

$router = new \Http\Router;

$router->get('/auth', function (\Http\Request $req, \Http\Response $res) {
    ['klk' => $klk] = $req->query();
    $res->status(404)->json($klk);
});

return $router;
