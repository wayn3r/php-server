<?php

use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase {

    public function testShouldNotBeAbleToUseNewKeyWord(){
        $this->expectError();
        $errorServer = new \Http\Server();
        
    }

    public function testGetServerShouldAlwaysReturnTheSameInstance(){
        $server = \Http\Server::getServer();
        $this->assertEquals($server, \Http\Server::getServer());
    }

    // TODO: test start() method
}
