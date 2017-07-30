<?php

namespace Dazzle\Http\Test\TModule\_Mock;

use Error;
use Exception;
use Dazzle\Event\BaseEventEmitter;
use Dazzle\Http\Http\HttpRequestInterface;
use Dazzle\Http\NetworkComponentInterface;
use Dazzle\Http\NetworkConnectionInterface;
use Dazzle\Http\NetworkMessageInterface;

class ComponentMock extends BaseEventEmitter implements NetworkComponentInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(NetworkConnectionInterface $conn)
    {
        $this->emit('connect', [ $conn ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
    {
        $this->emit('disconnect', [ $conn ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {
        $this->emit('message', [ $conn, $message ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
    {
        $this->emit('error', [ $conn, $ex ]);
    }
}
