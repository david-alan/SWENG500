<?php

namespace Tests\Fixtures;

$dir = 'vendor/';
var_dump(scandir($dir));
require 'vendor/autoload.php';

use Guzzle\Http\Client;

$postBody = file_get_contents(__DIR__ . '/../Fixtures/testData.json');

$client = new Client('http://54.67.71.88');
$request = $client->post('/broadcast', [], $postBody);
$response = $request->send();
echo $response->getBody();