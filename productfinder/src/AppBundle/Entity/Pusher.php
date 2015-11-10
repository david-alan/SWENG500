<?php
namespace AppBundle\Entity;

use Ratchet\ConnectionInterface;

use Thruway\Peer\Client;
use Thruway\Message\Message;
use Thruway\Transport\TransportInterface;
use Thruway\Transport\TransportProviderInterface;

class Pusher extends Client {

    public function onSessionStart($session, $transport)
    {
        echo "\n" . __METHOD__ . " was called.\n";
    }

    public function onOpen(TransportInterface $transport)
    {
        echo "\n" . __METHOD__ . " was called.\n";
    }

    public function onMessage(TransportInterface $transport, Message $msg)
    {
        echo "\n" . __METHOD__ . " was called.\n";
    }

    public function onClose($reason)
    {echo "\n" . __METHOD__ . " was called.\n";}

    public function addTransportProvider(TransportProviderInterface $transportProvider)
    {echo "\n" . __METHOD__ . " was called.\n";}

    public function start($startLoop = true)
    {echo "\n" . __METHOD__ . " was called.\n";}

    public function setAttemptRetry($attemptRetry)
    {echo "\n" . __METHOD__ . " was called.\n";}

/*
    protected $subscribedTopics = array();

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
        echo "onUnSsubscribed  called\n";
    }


    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        echo "onCall called\n";
        // In this application if clients send data it's because the user hacked around in console
       // $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        echo "onPublish  called\n";
        $topic->broadcast($event);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "onError called\n";
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        echo "onSubscribe called\n";
        $this->subscribedTopics[$topic->getId()] = $topic;
    }
*/
}