<?php

namespace Dazzle\Http\Test\TUnit\Http\Driver\Reader;

use Dazzle\Throwable\Exception\Logic\InvalidFormatException;
use Dazzle\Throwable\Exception\Runtime\ReadException;
use Dazzle\Http\Http\Driver\Parser\HttpParser;
use Dazzle\Http\Http\Driver\Reader\HttpReader;
use Dazzle\Http\Http\Driver\Reader\HttpReaderInterface;
use Dazzle\Util\Buffer\Buffer;
use Dazzle\Http\Test\TUnit;
use Exception;
use StdClass;

class HttpReaderTest extends TUnit
{
    /**
     * @var HttpReader
     */
    private $reader;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $reader = $this->createReader();

        $this->assertInstanceOf(HttpReader::class, $reader);
        $this->assertInstanceOf(HttpReaderInterface::class, $reader);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $reader = $this->createReader();
        unset($reader);
    }

    /**
     *
     */
    public function testApiReadRequest_CallsMethodOnModel_WhenEOMIsFound()
    {
        $text   = 'Text';
        $buffer = new Buffer($text);
        $data   = 'Data' . HttpReader::HTTP_EOM;
        $result = new StdClass;

        $reader = $this->createReader();
        $parser = $this->createParser();
        $parser
            ->expects($this->once())
            ->method('parseRequest')
            ->with($text . $data)
            ->will($this->returnValue($result));

        $this->assertSame($result, $reader->readRequest($buffer, $data));
    }

    /**
     *
     */
    public function testApiReadRequest_ThrowsException_WhenEOMIsNotFoundAndLengthLimitIsReached()
    {
        $text   = 'Text';
        $buffer = new Buffer($text);
        $data   = 'Data';

        $reader = $this->createReader([ 'maxFrameSize' => 0 ]);
        $parser = $this->createParser();
        $parser
            ->expects($this->never())
            ->method('parseRequest');

        $this->setExpectedException(ReadException::class);
        $reader->readRequest($buffer, $data);
    }

    /**
     *
     */
    public function testApiReadRequest_ThrowsException_WhenParserThrowsException()
    {
        $text   = 'Text';
        $buffer = new Buffer($text);
        $data   = 'Data' . HttpReader::HTTP_EOM;

        $reader = $this->createReader();
        $parser = $this->createParser();
        $parser
            ->expects($this->once())
            ->method('parseRequest')
            ->with($text . $data)
            ->will($this->throwException(new Exception));

        $this->setExpectedException(InvalidFormatException::class);
        $reader->readRequest($buffer, $data);
    }

    /**
     *
     */
    public function testApiReadResponse_CallsMethodOnModel_WhenEOMIsFound()
    {
        $text   = 'Text';
        $buffer = new Buffer($text);
        $data   = 'Data' . HttpReader::HTTP_EOM;
        $result = new StdClass;

        $reader = $this->createReader();
        $parser = $this->createParser();
        $parser
            ->expects($this->once())
            ->method('parseResponse')
            ->with($text . $data)
            ->will($this->returnValue($result));

        $this->assertSame($result, $reader->readResponse($buffer, $data));
    }

    /**
     *
     */
    public function testApiReadResponse_ThrowsException_WhenEOMIsNotFoundAndLengthLimitIsReached()
    {
        $text   = 'Text';
        $buffer = new Buffer($text);
        $data   = 'Data';

        $reader = $this->createReader([ 'maxFrameSize' => 0 ]);
        $parser = $this->createParser();
        $parser
            ->expects($this->never())
            ->method('parseResponse');

        $this->setExpectedException(ReadException::class);
        $reader->readResponse($buffer, $data);
    }

    /**
     *
     */
    public function testApiReadResponse_ThrowsException_WhenParserThrowsException()
    {
        $text   = 'Text';
        $buffer = new Buffer($text);
        $data   = 'Data' . HttpReader::HTTP_EOM;

        $reader = $this->createReader();
        $parser = $this->createParser();
        $parser
            ->expects($this->once())
            ->method('parseResponse')
            ->with($text . $data)
            ->will($this->throwException(new Exception));

        $this->setExpectedException(InvalidFormatException::class);
        $reader->readResponse($buffer, $data);
    }

    /**
     * @param string[]|null $methods
     * @return HttpParser|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createParser($methods = [])
    {
        $parser = $this->getMock(HttpParser::class, $methods, [], '', false);

        $this->setProtectedProperty($this->reader, 'parser', $parser);

        return $parser;
    }

    /**
     * @param mixed[] $options
     * @return HttpReader
     */
    public function createReader($options = [])
    {
        $this->reader = new HttpReader($options);

        return $this->reader;
    }
}
