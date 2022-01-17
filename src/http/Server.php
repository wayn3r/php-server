<?php

namespace Http;

final class Server extends \Http\Router {

    private static \Http\Server $server;

    private function __construct() {
    }

    public static function getServer(): \Http\Server {
        return self::$server ??= new \Http\Server;
    }

    private function getRequestBody() {
        // obteniendo los datos del cuerpo de la peticion
        $body = (json_decode(file_get_contents('php://input'), true) ?? []);
        return array_merge($body, $_POST);
    }

    private function getRequestQuery() {
        return $_GET;
    }

    private function notFoundURL() {
        return function (\Http\Request $req, \Http\Response $res) {
            $res->status(404)
                ->send('Cannot ' . $req->method() . ' ' . $req->fullUrl());
        };
    }

    public function start(): void {
        $this->use($this->notFoundURL());
        $query = $this->getRequestQuery();
        $body = $this->getRequestBody();
        $request = new \Http\Request($body, $query);
        $response = new \Http\Response;
        $this->__invoke($request, $response);
    }
}
