<?php

use PHPUnit\Framework\TestCase;
use Phpunit\Framework\MockObject\MockObject;
use Http\Server;
use Http\Response;
use Http\Request;

class ServerTest extends TestCase {

    public function testShouldNotBeAbleToUseNewKeyWord() {
        $this->expectError();
        $errorServer = new Server();

    }

    public function testGetServerShouldAlwaysReturnTheSameInstance() {
        $server = Server::getServer();
        $this->assertEquals($server, Server::getServer());
    }

    public function testShouldCallInvokeWithNewRequestAndResponseWhenStart() {
        $mockedMethod = '__invoke';
        /** @var MockObject | Server $server */
        $server = $this->createPartialMock(Server::class, [$mockedMethod]);
        $server
            ->expects($this->once())
            ->method($mockedMethod)
            ->with(new Request([], []), new Response);
        $server->start();
    }
}
