<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

$app = \Http\Server::getServer();

$app->start();
