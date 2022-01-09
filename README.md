# wayn3r/php-server

Es un router para PHP para la creación de APIs inspirado en express.js

## Ejemplo de uso basico:

En el archivo entrada del proyecto (index.php)

```
<?php

require_once  <project-root> '/vendor/autoload.php';

$app = \Http\Server::getServer();

$app->get('/', 
    function(\Http\Request $req, \Http\Response $res){
        $res->send('Hello world');
    }
);

$app->start();

```

Para hacer funcionar este router todas las peticiones al servidor deben ser procesadas por el entrada del proyecto (index.php).

Esto se puede conseguir usando el comando de php para levantar un servidor
```
php -S localhost:8080 -c php.ini index.php
```
Si usas Apache puedes conseguir esto a traves del archivo .htaccess
```
RewriteEngine on

RewriteRule ^(.*)$ index.php [L,QSA]
```

## Rutas relativas

Puedes hacer uso de rutas relativas creando instancias de \Http\Router y usandolas como middleware en la app, tal y como se haria en express.

En fichero router.php
```
<?php

$router = new \Http\Router;

$router->get('/', function(\Http\Request $req, \Http\Response $res){
    $res->json([
        'message' => 'Hello world from user router'
    ]);
});

return $router;
``` 

En el index.php
```
<?php

require_once  <project-root> '/vendor/autoload.php';

$userRouter = require('router.php');

$app = \Http\Server::getServer();

$app->use('/user', $userRouter);

$app->start();

```

## Uso de query, body y url params

Para acceder a los Query Params y a los parametros pasados en el cuerpo de la petición, el \Http\Request cuenta con dos metodos query() y body():
```
<?php

$router = new \Http\Router;

$router->get('/', function(\Http\Request $req, \Http\Response $res){
    $query = $req->query();
    $body = $req->body();
    $res->json([
        'query' => $query,
        'body' => $body
    ]);
});

return $router;
```
Tambien puedes recibir datos por la url de forma similar a como lo hace express
```
<?php

$router = new \Http\Router;

$router->get('/:id', function(\Http\Request $req, \Http\Response $res){
    ['id' => $id] = $req->params();
    $query = $req->query();
    $body = $req->body();
    $res->json([
        'query' => $query,
        'body' => $body,
        'id' => $id
    ]);
});

return $router;
```
Para especificar un Url Param debe agregar : y seguido el nombre del parametro. Note que los : deben siempre estar despues de un /, de no hacerlo asi podria llevar a un comportamiento no deseado, ejemplo:

/user/:id -> id es un Url Param requerido para matchear al controlador.
/user:id -> se necesita una petición tal que /user:id para matchear al controlador.

## Uso de Middlewares

Los middlewares son tratados igual que los controladores, de hecho es la logica que se implemente dentro la que hace la diferencia.
Todos los controladores reciben como parametros el \Http\Request, el \Http\Response, y un callable, este ultimo parametro actua igual que la función next de express y te lleva al siguiente controlador en la cola.
Este callable puede recibir tantos parametros como el usuario quiera y seran pasador al siguiente controlador antes del \Http\Request. 
Esto es importante porque pasar parametros o no a la funcion next cambiara el orden de los parametros recibidos en el siguiente controlador, veamos un ejemplo:

```
<?php

$router = new \Http\Router;

$router->get('/', function(\Http\Request $req, \Http\Response $res, callable $next){
    $id = $req->query()['id'];
    if(!$id) return $next('El parametro id es requerido');
    $res->json([
        'id' => $id
    ]);
});

$router->post('/', function(\Http\Request $req, \Http\Response $res, callable $next){
    $name = $req->query()['name'];
    if(!$name) return $next('El parametro name es requerido');
    $res->json([
        'name' => $name
    ]);
});

$router->use('/', function($errors, $_, \Http\Response $res){
    $res->status(400)->json([
        'error' => $errors
    ]);
});

return $router;
```
Igual que en express tambien se pueden agregar middlewares a nivel de petición.
```
<?php

$router = new \Http\Router;

$router->get('/', 
    function(\Http\Request $req, \Http\Response $res, callable $next){
        $id = $req->query()['id'];
        if(!$id || !is_numeric($id)) 
            return $res->status(400)->json([
                'error' => 'ID invalido'
            ]); 
        $req->id = intval($id);
        $next();
    },
    function(\Http\Request $req, \Http\Response $res){
        $id = $req->id;
        $res->json([
            'id' => $id
        ]);
    }
);

return $router;

```

## Validators

php-server ya viene con algunos middlewares para validar parecidos al funcionamiento de express-validator. Replicando en el ejemplo anterior pero usando Validator se veria asi:

Nota: Esta caracteristica aun esta en desarrollo.
```
<?php

$router = new \Http\Router;

$validator = new \Validate\Validator;

$router->get('/', 
    $validator->required()->number()->query('id'),
    $validator->checkout(),
    function(\Http\Request $req, \Http\Response $res){
        $id = intval($req->query()['id']);
        $res->json([
            'id' => $id
        ]);
    }
);

return $router;

```
