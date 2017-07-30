<?php

namespace Dazzle\Http\Test\TUnit\Null;

use Exception;
use Dazzle\Http\Null\NullServer;
use Dazzle\Http\Test\TUnit;
use Dazzle\Http\NetworkConnectionInterface;
use Dazzle\Http\NetworkMessageInterface;

class NullServerTest extends TUnit
{
    /**
     *
     */
    public function testApiHandleConnect_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);

        $server->handleConnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);

        $server->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleMessage_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $mssg = $this->getMock(NetworkMessageInterface::class, [], [], '', false);

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleError_DoesNothing()
    {
        $server = $this->createServer();
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $ex = new Exception();

        $server->handleError($conn, $ex);
    }

    /**
     * @return NullServer
     */
    public function createServer()
    {
        return new NullServer();
    }
}
