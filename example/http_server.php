<?php

use Dazzle\Http\Http\HttpResponse;
use Dazzle\Http\Http\HttpServer;
use Dazzle\Http\Socket\SocketServer;
use Dazzle\Http\NetworkMessageInterface;
use Dazzle\Http\NetworkComponentInterface;
use Dazzle\Http\NetworkConnectionInterface;
use Dazzle\Loop\Model\SelectLoop;
use Dazzle\Loop\Loop;
use Dazzle\Socket\SocketListener;


require __DIR__ . '/bootstrap/autoload.php';

class ServerComponent implements NetworkComponentInterface
{
    public function handleConnect(NetworkConnectionInterface $conn)
    {}

    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {
        $response = new HttpResponse(
            200,
            [
                'X-Powered-By' => 'DazzlePHP',
            ],
            sprintf("Hell Yeah! [%s]", str_pad(rand(1,1000000), 7, '0', STR_PAD_LEFT))
        );
        $conn->send((string) $response);
        $conn->close();
    }

    public function handleDisconnect(NetworkConnectionInterface $conn)
    {}

    public function handleError(NetworkConnectionInterface $conn, $ex)
    {}
}

$loop = new Loop(new SelectLoop());
$listener = new SocketListener('tcp://127.0.0.1:2080', $loop);
$listener->start();

$http = new HttpServer(
    $server    = new SocketServer($listener),
    $component = new ServerComponent()
);

$loop->start();
