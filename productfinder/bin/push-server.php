<?php
namespace bin\push;

require dirname(__DIR__) . '/vendor/autoload.php';
use AppBundle\Entity\Pusher;
use React\EventLoop\Factory;
use React\Socket\Server;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;

use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;

$router = new Router();
$realm = "realm1";

$router->addInternalClient(new Pusher($realm, $router->getLoop()));

$router->addTransportProvider(new RatchetTransportProvider("0.0.0.0", 8080));

$router->start();die();