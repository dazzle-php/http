<?php

namespace Dazzle\Http\Http\Driver\Parser;

use Dazzle\Http\Http\HttpRequestInterface;
use Dazzle\Http\Http\HttpResponseInterface;
use Exception;

interface HttpParserInterface
{
    /**
     * Parse given string and return HttpRequestInterface object.
     *
     * @param string $message
     * @return HttpRequestInterface
     * @throws Exception
     */
    public function parseRequest($message);

    /**
     * Parse given string and return HttpResponseInterface object.
     *
     * @param string $message
     * @return HttpResponseInterface
     * @throws Exception
     */
    public function parseResponse($message);
}
