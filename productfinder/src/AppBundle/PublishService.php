<?php

namespace AppBundle;

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class PublishService
{
    public function __construct($json)
    {
        $client = new Client("product_realm");
        $client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080/"));

        $jsonObject = json_decode($json);
        $tube = $jsonObject->{'searchTerm'};

        $client->on('open', function (ClientSession $session) use ($json, $tube) {
            // publish an event
            $session->publish($tube, [$json], [], ["acknowledge" => true])->then(
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