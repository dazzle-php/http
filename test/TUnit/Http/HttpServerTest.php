<?php

namespace Dazzle\Http\Test\TUnit\Socket;

use Dazzle\Http\Http\Driver\HttpDriver;
use Dazzle\Http\Http\Driver\HttpDriverInterface;
use Dazzle\Http\Http\HttpRequest;
use Dazzle\Http\Http\HttpResponseInterface;
use Dazzle\Http\Http\HttpServer;
use Dazzle\Http\Http\HttpServerInterface;
use Dazzle\Http\Null\NullServer;
use Dazzle\Http\NetworkComponentAwareInterface;
use Dazzle\Http\NetworkComponentInterface;
use Dazzle\Http\NetworkConnectionInterface;
use Dazzle\Http\NetworkMessage;
use Dazzle\Http\NetworkMessageInterface;
use Dazzle\Util\Buffer\Buffer;
use Dazzle\Http\Test\TUnit;
use Exception;

class HttpServerTest extends TUnit
{
    /**
     * @var HttpServer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $server;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $this->assertInstanceOf(HttpServer::class, $server);
        $this->assertInstanceOf(HttpServerInterface::class, $server);
        $this->assertInstanceOf(NetworkComponentAwareInterface::class, $server);
        $this->assertInstanceOf(NetworkComponentInterface::class, $server);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        unset($server);
    }

    /**
     *
     */
    public function testApiGetDriver_ReturnsDriver()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $this->assertInstanceOf(HttpDriverInterface::class, $server->getDriver());
    }

    /**
     *
     */
    public function testApiSetComponent_SetsComponent_WhenComponentIsProvided()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $server->setComponent($new = $this->createComponent());
        $this->assertSame($new, $server->getComponent());
    }

    /**
     *
     */
    public function testApiSetComponent_SetsNullComponent_WhenComponentIsNotProvided()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $server->setComponent();
        $this->assertInstanceOf(NullServer::class, $server->getComponent());
    }

    /**
     *
     */
    public function testApiGetComponent_ReturnsComponent()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $this->assertSame($component, $server->getComponent());
    }

    /**
     *
     */
    public function testApiHandleConnect_SetsConnectionFlags()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);

        $server->handleConnect($conn);

        $this->assertInstanceOf(Buffer::class, $conn->httpBuffer);
        $this->assertSame(false, $conn->httpHeadersReceived);
        $this->assertSame(null,  $conn->httpRequest);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_CallsMethodOnComponent_WhenHeadersAreReceived()
    {
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn->httpHeadersReceived = true;

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleDisconnect')
            ->with($conn);

        $server = $this->createServer($aware, $component);
        $server->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleDisconnect_DoesNothing_WhenHeadersAreNotReceived()
    {
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn->httpHeadersReceived = false;

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleDisconnect');

        $server = $this->createServer($aware, $component);
        $server->handleDisconnect($conn);
    }

    /**
     *
     */
    public function testApiHandleMessage_EncodesHeaders_WhenHeadersAreBeingReceived()
    {
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn->httpBuffer = new Buffer();
        $conn->httpHeadersReceived = false;

        $mssg = new NetworkMessage($text = 'text');
        $req  = $this->getMock(HttpRequest::class, [], [], '', false);

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleConnect')
            ->with($conn);
        $component
            ->expects($this->once())
            ->method('handleMessage')
            ->with($conn, $req);

        $server = $this->createServer($aware, $component);

        $driver = $this->createDriver();
        $driver
            ->expects($this->once())
            ->method('readRequest')
            ->with($conn->httpBuffer, $text)
            ->will($this->returnValue($req));

        $server->handleMessage($conn, $mssg);

        $this->assertSame(true, $conn->httpHeadersReceived);
        $this->assertSame($req, $conn->httpRequest);
    }

    /**
     *
     */
    public function testApiHandleMessage_ClosesConnection_WhenHeadersAreInvalid()
    {
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn->httpBuffer = new Buffer();
        $conn->httpHeadersReceived = false;

        $mssg = new NetworkMessage($text = 'text');

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->never())
            ->method('handleConnect');
        $component
            ->expects($this->never())
            ->method('handleMessage');

        $server = $this->createServer($aware, $component, [ 'close' ]);
        $server
            ->expects($this->once())
            ->method('close')
            ->with($conn, 413);

        $driver = $this->createDriver();
        $driver
            ->expects($this->once())
            ->method('readRequest')
            ->with($conn->httpBuffer, $text)
            ->will($this->throwException(new Exception));

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleMessage_PropagatesMessage_WhenHeadersAreReceived()
    {
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn->httpHeadersReceived = true;

        $mssg = $this->getMock(NetworkMessageInterface::class, [], [], '', false);

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleMessage')
            ->with($conn, $mssg);

        $server = $this->createServer($aware, $component);

        $server->handleMessage($conn, $mssg);
    }

    /**
     *
     */
    public function testApiHandleError_CallsMethodOnComponent_WhenHeadersAreReceived()
    {
        $ex = new Exception();
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn->httpHeadersReceived = true;

        $aware     = $this->createAware();
        $component = $this->createComponent();
        $component
            ->expects($this->once())
            ->method('handleError')
            ->with($conn, $ex);

        $server = $this->createServer($aware, $component);
        $server->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testApiHandleError_ClosesConnection_WhenHeadersAreNotReceived()
    {
        $ex = new Exception();
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn->httpHeadersReceived = false;

        $aware     = $this->createAware();
        $component = $this->createComponent();

        $server = $this->createServer($aware, $component, [ 'close' ]);
        $server
            ->expects($this->once())
            ->method('close')
            ->with($conn, 500);

        $server->handleError($conn, $ex);
    }

    /**
     *
     */
    public function testProtectedApiClose_ClosesSocket()
    {
        $aware     = $this->createAware();
        $component = $this->createComponent();
        $server = $this->createServer($aware, $component);

        $code = 300;
        $conn = $this->getMock(NetworkConnectionInterface::class, [], [], '', false);
        $conn
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(HttpResponseInterface::class));
        $conn
            ->expects($this->once())
            ->method('close');

        $this->callProtectedMethod($server, 'close', [ $conn, $code ]);
    }

    /**
     * @param string[]|null $methods
     * @return HttpDriver|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createDriver($methods = [])
    {
        $driver = $this->getMock(HttpDriver::class, $methods, [], '', false);

        $this->setProtectedProperty($this->server, 'httpDriver', $driver);

        return $driver;
    }

    /**
     * @return NetworkComponentAwareInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createAware()
    {
        return $this->getMock(NetworkComponentAwareInterface::class, [], [], '', false);
    }

    /**
     * @return NetworkComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createComponent()
    {
        return $this->getMock(NetworkComponentInterface::class, [], [], '', false);
    }

    /**
     * @param NetworkComponentAwareInterface $aware
     * @param NetworkComponentInterface $component
     * @param string[]|null $methods
     * @return HttpServer|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createServer($aware, $component, $methods = null)
    {
        $this->server = $this->getMock(HttpServer::class, $methods, [ $aware, $component ]);

        return $this->server;
    }
}
