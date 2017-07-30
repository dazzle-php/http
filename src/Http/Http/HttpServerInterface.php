<?php

namespace Dazzle\Http\Http;

use Dazzle\Http\Http\Driver\HttpDriverInterface;
use Dazzle\Http\NetworkComponentInterface;

interface HttpServerInterface extends NetworkComponentInterface
{
    /**
     * Return current driver.
     *
     * @return HttpDriverInterface
     */
    public function getDriver();
}
