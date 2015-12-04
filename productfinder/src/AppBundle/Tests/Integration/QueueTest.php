<?php

namespace Tests\Integration;

use AppBundle\Services\QueueService;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    //TODO: get this from a YAML file
    //we need a separate testing environment

    protected $host = '54.67.71.88';
    protected $port = 5672;
    protected $username = 'queue_user';
    protected $password = 'BVfDqRGK9Y3G';

    public function testEnqueueProduct($searchTerm = 'unit_test_123')
    {
        $success = $this->qs->enqueue($searchTerm);
        $this->assertTrue($success);
    }

    public function testDequeueProduct()
    {
        $searchTerm = 'unit_test_123';
        $queueName = 'products';
        $this->testEnqueueProduct($searchTerm);

        $callback = function($msg){
            echo " [x] Received ", $msg->body, "\n";
            echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $connection = new AMQPStreamConnection($this->host, $this->port, $this->username, $this->password);
        $channel = $connection->channel();
        $success = $channel->basic_consume($queueName, '', false, false, false, false, $callback);

    }

    public function setUp()
    {
        $this->qs = new QueueService($this->host, $this->port, $this->username, $this->password);
    }

}
