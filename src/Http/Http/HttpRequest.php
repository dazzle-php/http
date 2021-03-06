<?php

namespace Dazzle\Http\Http;

use Dazzle\Http\NetworkMessageInterface;
use GuzzleHttp\Psr7\Request;

class HttpRequest extends Request implements HttpRequestInterface, NetworkMessageInterface
{
    /**
     * @override
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->encode();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function encode()
    {
        return sprintf(
            "%s %s HTTP/%s\r\n%s\r\n%s",
            $this->getMethod(),
            $this->getTarget(),
            $this->getProtocolVersion(),
            $this->encodeHeaders($this->getHeaders()),
            (string) $this->getBody()
        );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getTarget()
    {
        return $this->getRequestTarget();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function read()
    {
        return (string) $this->getBody();
    }

    /**
     * Encode headers.
     *
     * @param string[][] $headers
     * @return string
     */
    protected function encodeHeaders($headers = [])
    {
        $data = '';

        foreach ($headers as $name=>$values)
        {
            $temp = [];
            $data .= $name . ": ";
            $values = (array) $values;

            foreach ($values as $value)
            {
                $temp[] = $this->encodeHeader($value);
            }

            $data .= implode(", ", $temp);
            $data .= "\r\n";
        }

        return $data;
    }

    /**
     * Encode single header.
     *
     * @param string $header
     * @return string
     */
    protected function encodeHeader($header)
    {
        return preg_replace_callback(
            '/(?:[^A-Za-z0-9_\-\.~!\$&\'\(\)\[\]\*\+,:;=\/% ]+|%(?![A-Fa-f0-9]{2}))/',
            function (array $matches) {
                return rawurlencode($matches[0]);
            },
            $header
        );
    }
}
