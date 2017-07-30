<?php

namespace Dazzle\Http\Null;

use Dazzle\Http\NetworkMessageInterface;
use Dazzle\Http\NetworkComponentInterface;
use Dazzle\Http\NetworkConnectionInterface;

class NullServer implements NetworkComponentInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(NetworkConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
    {}
}
