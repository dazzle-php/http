<?php

namespace Dazzle\Http;

interface NetworkMessageInterface
{
    /**
     * Return original message as string.
     *
     * @return string
     */
    public function read();
}
