<?php

namespace Tests\Integration;

use Guzzle\Http\Client;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    //TODO: make this localhost,
    //my dev environment isn't setup correctly
    //at this time
    protected $address = '54.67.71.88';

    public function testPublishToWebSocket()
    {
        $postBody = file_get_contents(__DIR__ . '/../Fixtures/testData.json');

        $client = new Client('http://' . $this->address);
        $request = $client->post('/broadcast', [], $postBody);
        $response = $request->send();
        $this->assertContains('Publish Acknowledged!', (string)$response->getBody());
    }

}
