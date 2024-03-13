<?php

namespace Tests\EndToEnd;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetTest extends KernelTestCase
{
    private HttpClientInterface $client;
    public function __construct()
    {
        parent::__construct();

        $this->client = HttpClient::create();
    }

    public function testGetReturnsAllItems()
    {
        $response = $this->client->request('GET', 'http://webserver/api/v1/tablets');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                [
                    'id' => '44682a67-fa83-4216-9e9d-5ea5dd5bf480',
                    'manufacturer' => 'Lenovo',
                    'model' => 'Tab M9',
                    'price' => 19900
                ],
                [
                    'id' => '5c82f07f-3a47-422b-b423-efc3b782ec56',
                    'manufacturer' => 'Asus',
                    'model' => 'MeMO Pad HD 7',
                    'price' => 3110
                ],
                [
                    'id' => '0bdea651-825f-4648-9cac-4b03f8f4576e',
                    'manufacturer' => 'Samsung',
                    'model' => 'Galaxy Tab A9+',
                    'price' => 24799
                ]
            ],
            json_decode($response->getContent())
        );
    }

    public function testGetReturnsSingleItem()
    {
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';

        $response = $this->client->request(
            'GET',
            "http://webserver/api/v1/tablets/$itemId"
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            [
                'manufacturer' => 'Lenovo',
                'model' => 'Tab M9',
                'price' => 19900
            ],
            json_decode($response->getContent())
        );
    }

    public function testGetFailsWithInvalidId()
    {
        $itemId = '66a5c0d8-4289-43ba-941a-e235f722c438';

        $response = $this->client->request('GET', "http://webserver/api/v1/tablets/$itemId");

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));
    }
}
