<?php

namespace Dazzle\Http;

use Dazzle\Socket\SocketListenerInterface;
use Dazzle\Loop\LoopInterface;
use Dazzle\Throwable\Exception\Logic\InstantiationException;
use Dazzle\Http\Http\Component\Router\HttpRouter;
use Dazzle\Http\Http\Component\Router\HttpRouterInterface;
use Dazzle\Http\Http\HttpServer;
use Dazzle\Http\Socket\Component\Firewall\SocketFirewall;
use Dazzle\Http\Socket\SocketServer;
use Dazzle\Http\Socket\SocketServerInterface;
use Error;
use Exception;

class NetworkServer implements NetworkServerInterface
{
    /**
     * @var SocketListenerInterface
     */
    protected $listener;

    /**
     * @var SocketServerInterface
     */
    protected $server;

    /**
     * @var HttpServer
     */
    protected $http;

    /**
     * @var SocketFirewall|null
     */
    protected $firewall;

    /**
     * @var HttpRouterInterface
     */
    public $router;

    /**
     * @param SocketListenerInterface $listener
     * @param mixed[] $params
     * @throws InstantiationException
     */
    public function __construct(SocketListenerInterface $listener, $params = [])
    {
        try
        {
            $router = new HttpRouter(
                $http = new HttpServer(
                    $server = new SocketServer($listener)
                ),
                $params
            );

            $this->listener = $listener;
            $this->server = $server;
            $this->http = $http;
            $this->firewall = null;
            $this->router = $router;
        }
        catch (Error $ex)
        {
            throw new InstantiationException("[" . __CLASS__ . "] could not be created.", 0, $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("[" . __CLASS__ . "] could not be created.", 0, $ex);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->router);
        unset($this->firewall);
        unset($this->http);
        unset($this->server);
        unset($this->listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsRoute($path)
    {
        return $this->router->existsRoute($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addRoute($path, NetworkComponentInterface $component)
    {
        return $this->router->addRoute($path, $component);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeRoute($path)
    {
        return $this->router->removeRoute($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function blockAddress($address)
    {
        if ($this->firewall === null)
        {
            $this->createFirewall();
        }

        $this->firewall->blockAddress($address);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unblockAddress($address)
    {
        if ($this->firewall === null)
        {
            $this->createFirewall();
        }

        $this->firewall->unblockAddress($address);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isAddressBlocked($address)
    {
        if ($this->firewall === null)
        {
            return false;
        }

        return $this->firewall->isAddressBlocked($address);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBlockedAddresses()
    {
        if ($this->firewall === null)
        {
            return [];
        }

        return $this->firewall->getBlockedAddresses();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        $this->listener->close();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function close()
    {
        $this->listener->close();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLoop()
    {
        return $this->listener->getLoop();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setLoop(LoopInterface $loop = null)
    {
        $this->listener->setLoop($loop);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPaused()
    {
        return $this->listener->isPaused();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pause()
    {
        $this->listener->pause();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resume()
    {
        $this->listener->resume();
    }

    /**
     * Create and attach firewall to transfer server Firewall.
     */
    protected function createFirewall()
    {
        $this->firewall = new SocketFirewall($this->server, $this->http);
    }
}
