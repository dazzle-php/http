<?php

namespace Dazzle\Http\Http\Driver;

use Dazzle\Http\Http\Driver\Reader\HttpReader;
use Dazzle\Http\Http\Driver\Reader\HttpReaderInterface;
use Dazzle\Util\Buffer\BufferInterface;

class HttpDriver implements HttpDriverInterface
{
    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @var HttpReaderInterface
     */
    protected $reader;

    /**
     * @param mixed[] $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
        $this->reader = new HttpReader($this->options);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->options);
        unset($this->reader);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function readRequest(BufferInterface $buffer, $message)
    {
        return $this->reader->readRequest($buffer, $message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function readResponse(BufferInterface $buffer, $message)
    {
        return $this->reader->readResponse($buffer, $message);
    }
}
