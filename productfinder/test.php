<?php
require_once 'vendor/autoload.php';

/**
 * Test the web sockets

use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

$json = '{ "name": "Sony XL123", "price" : "123.45", "rating" : "3/5", "image" : "http://asdf.com/example.png", "websiteURL" : "http://example.com/electronics/headphones/abc", "vendor": "Amazon", "description": "Lorem ipsum...", "searchterm": "headphones"}';

$client = new Client("product_realm");
$client->addTransportProvider(new PawlTransportProvider("ws://127.0.0.1:8080/"));

$client->on('open', function (ClientSession $session) use ($json) {

    // 2) publish an event
    $session->publish('product',  [$json], [], ["acknowledge" => true])->then(
        function () use ($json) {
            echo "json var is: ";
            var_dump($json);
            echo "Publish Acknowledged!\n";
           // die(); //??? need to die out to keep it from going forever?
        },
        function ($error) {
            // publish failed
            echo "Publish Error {$error}\n";
        }
    );

});


$client->start();
 *
 * */

/*
 * Test the Message queue

 */

use PhpAmqpLib\Connection\AMQPStreamConnection;

$queueName = 'products';
$connection = new AMQPStreamConnection('localhost', 5672, 'queue_user', 'BVfDqRGK9Y3G');
$channel = $connection->channel();

$channel->queue_declare($queueName, false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function($msg){
    echo " [x] Received ", $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done", "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume($queueName, '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
