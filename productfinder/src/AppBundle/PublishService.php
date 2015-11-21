<?php

namespace AppBundle;

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class PublishService extends Controller\DefaultController
{
    public function __construct($host, $port, $realm)
    {
        $this->host  = $host;
        $this->port  = $port;
        $this->realm = $realm;
    }

    public function sendPayload($payload)
    {
        $client = new Client($this->realm);
        $client->addTransportProvider(new PawlTransportProvider("ws://{$this->host}:{$this->port}/"));

        $jsonObject = json_decode($payload);
        $tube = $jsonObject->{'searchTerm'};

$logger = $this->get('logger');
$logger->error("tube is: " . $tube);
$logger->error($jsonObject);
$logger->error($this->host);
$logger->error($this->port);
$logger->error($this->realm);

        $client->on('open', function (ClientSession $session) use ($payload, $tube) {
            // publish an event
            $session->publish($tube, [$payload], [], ["acknowledge" => true])->then(
                function () {
                    echo "Publish Acknowledged!\n";
                    die(); //??? need to die out to keep it from going forever?
                },
                function ($error) {
                    // publish failed
                    echo "Publish Error {$error}\n";
                }
            );
        });
        $client->start();
    }
}