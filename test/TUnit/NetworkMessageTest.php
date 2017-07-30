<?php

namespace Dazzle\Http\Test\TUnit;

use Dazzle\Http\NetworkMessage;
use Dazzle\Http\NetworkMessageInterface;
use Dazzle\Http\Test\TUnit;

class IoMessageTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $message = $this->createIoMessage('');

        $this->assertInstanceOf(NetworkMessage::class, $message);
        $this->assertInstanceOf(NetworkMessageInterface::class, $message);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $message = $this->createIoMessage('');
        unset($message);
    }

    /**
     *
     */
    public function testApiRead_ReturnsStoredMessage()
    {
        $message = $this->createIoMessage($text = 'text');
        $this->assertSame($text, $message->read());
    }

    /**
     * @return NetworkMessage
     */
    public function createIoMessage($message)
    {
        return new NetworkMessage($message);
    }
}
