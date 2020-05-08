<?php

use PHPUnit\Framework\TestCase;

require('vendor/autoload.php');

class BooksTest extends TestCase
{
    protected $client;

    protected function setUp():void
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://testproject.ipd20:8888'
        ]);
    }

    public function testGet_ValidInput_BookObject()
    {
        $response = $this->client->get('/jsondata');

        //$data = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
    }
}