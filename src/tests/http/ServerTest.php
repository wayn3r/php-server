<?php

use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase {
    public function testGetServerShouldReturnTheSameInstance(){
        $server = \Http\Server::getServer();
        $this->assertEquals($server, \Http\Server::getServer());
    }
}
