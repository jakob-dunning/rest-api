<?php

namespace EndToEnd\TabletApi;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetTest extends KernelTestCase
{
    private HttpClientInterface $client;

    public function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    /**
     * @covers \App\Controller\TabletApiController::list
     */
    public function testGetReturnsAllItems()
    {
        $response = $this->client->request('GET', 'http://webserver/api/tablets');

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
            json_decode($response->getContent(), true)
        );
    }

    /**
     * @covers \App\Controller\TabletApiController::show
     */
    public function testGetReturnsSingleItem()
    {
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';

        $response = $this->client->request(
            'GET',
            "http://webserver/api/tablets/$itemId"
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            [
                'id' => $itemId,
                'manufacturer' => 'Lenovo',
                'model' => 'Tab M9',
                'price' => 19900
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testGetFailsWithInvalidId()
    {
        $itemId = '66a5c0d8-4289-43ba-941a-e235f722c438';

        $response = $this->client->request('GET', "http://webserver/api/tablets/$itemId");

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Invalid uuid'], json_decode($response->getContent(), true));
    }
}
