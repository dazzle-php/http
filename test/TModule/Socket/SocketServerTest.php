<?php

namespace Dazzle\Http\Test\TModule\Socket;

use Dazzle\Socket\Socket;
use Dazzle\Socket\SocketListener;
use Dazzle\Loop\LoopInterface;
use Dazzle\Http\Socket\SocketServer;
use Dazzle\Http\NetworkComponentInterface;
use Dazzle\Http\NetworkConnectionInterface;
use Dazzle\Http\NetworkMessageInterface;
use Dazzle\Http\Test\TModule\_Mock\ComponentMock;
use Dazzle\Http\Test\TModule;
use Dazzle\Http\Test\_Simulation\SimulationInterface;

class SocketServerTest extends TModule
{
    /**
     * @var resource
     */
    private $endpoint = 'tcp://127.0.0.1:10080';

    /**
     * @var SocketListener
     */
    private $listener = null;

    /**
     * @var SocketServer
     */
    private $server = null;

    /**
     *
     */
    public function tearDown()
    {
        $this->listener = null;
        $this->server   = null;

        parent::tearDown();
    }

    /**
     *
     */
    public function testCaseSocketServer_HandlesIncomingMessages()
    {
        $this
            ->simulate(function(SimulationInterface $sim) {
                $component = $this->createComponent();
                $loop      = $sim->getLoop();
                $server    = $this->createServer($component, $loop);
                $client    = $this->createClient($loop);

                $server->start();

                $component->on('connect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('connect');
                });

                $component->on('disconnect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('disconnect');
                });

                $component->on('message', function(NetworkConnectionInterface $conn, NetworkMessageInterface $message) use($sim) {
                    $sim->expect('message', [ $message->read() ]);
                    $sim->done();
                });

                $sim->onStart(function() use($client) {
                    $client->write('multipart');
                    $client->write('rawdata');
                });
                $sim->onStop(function() use($client, $server) {
                    $client->stop();
                    $server->stop();
                });

                unset($server);
                unset($component);
                unset($loop);
            })
            ->expect([
                [ 'connect' ],
                [ 'message', [ 'multipartrawdata' ] ],
                [ 'disconnect' ]
            ]);
    }

    //todo
    public function toDotestCaseSslSocketServer_HandlesIncomingMessages()
    {
        $this
            ->simulate(function(SimulationInterface $sim) {
                $component = $this->createComponent();
                $loop      = $sim->getLoop();
                $server    = $this->createSslServer($component, $loop);
                $client    = $this->createSslClient($loop);

                $server->start();

                $component->on('connect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('connect');
                });

                $component->on('disconnect', function(NetworkConnectionInterface $conn) use($sim) {
                    $sim->expect('disconnect');
                });

                $component->on('message', function(NetworkConnectionInterface $conn, NetworkMessageInterface $message) use($sim) {
                    $sim->expect('message', [ $message->read() ]);
                    $sim->done();
                });

                $sim->onStart(function() use($client) {
                    $client->write('multipart');
                    $client->write('rawdata');
                });
                $sim->onStop(function() use($client, $server) {
                    $client->stop();
                    $server->stop();
                });

                unset($server);
                unset($component);
                unset($loop);
            })
            ->expect([
                [ 'connect' ],
                [ 'message', [ 'multipartrawdata' ] ],
                [ 'disconnect' ]
            ]);
    }

    /**
     * @return ComponentMock
     */
    public function createComponent()
    {
        return new ComponentMock();
    }

    /**
     * @param LoopInterface $loop
     * @return Socket
     */
    public function createClient(LoopInterface $loop)
    {
        return new Socket($this->endpoint, $loop);
    }

    public function createSslClient(LoopInterface $loop)
    {
        $config = [
            'ssl' => true,
            'cafile'=>realpath(__DIR__.'/../../../../test/cert.pem'),
        ];

        return new Socket('ssl://127.0.0.1:10080', $loop, $config);
    }

    /**
     * @param NetworkComponentInterface $component
     * @param LoopInterface $loop
     * @return SocketServer
     */
    public function createServer(NetworkComponentInterface $component, LoopInterface $loop)
    {
        $this->listener = new SocketListener($this->endpoint, $loop);
        $this->server   = new SocketServer($this->listener, $component);
        $this->server->start();

        return $this->server;
    }

    public function createSslServer(NetworkComponentInterface $component, LoopInterface $loop)
    {
        $config = [
            'ssl' => true,
            'local_cert'=>realpath(__DIR__.'/../../../../test/cert.pem'),
            'passphrase'=>'secret',
            'local_pk'=>realpath(__DIR__.'/../../../../test/key.pem'),
        ];

        $this->listener = new SocketListener('ssl://127.0.0.1:10080', $loop, $config);
        $this->server   = new SocketServer($this->listener, $component);
        $this->server->start();

        return $this->server;
    }
}
