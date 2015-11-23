<?php

namespace AppBundle\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueService
{
    public function __construct($host, $port, $username, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function enqueue($searchTerm)
    {
        //If you get permissions issues on rabbitMQ, you can open everything
        //up with this command (and enable security vulnerabilities):
        //sudo rabbitmqctl set_user_permissions queue_user ".*" ".*" ".*"
        $queueName ='products';
        $queueValue = $searchTerm;

        $connection = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password);

        $channel = $connection->channel();
        $channel->queue_declare($queueName, false, true, false, false);

        $msg = new AMQPMessage($queueValue,
            array('delivery_mode' => 2) # make message persistent (flush to disk)
        );

        $channel->basic_publish($msg, '', $queueName);

        $channel->close();
        $connection->close();
    }
}
