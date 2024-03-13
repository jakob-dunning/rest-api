<?php

namespace Tests\EndToEnd;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetTest extends \PHPUnit\Framework\TestCase
{
    private HttpClientInterface $client;
    public function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    public function testGetReturnsAllItems()
    {
        // TODO: add test data
        $response = $this->client->request('GET', 'http://webserver/api/v1/tablets');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                [
                    'manufacturer' => 'Lenovo',
                    'model' => 'Tab M9',
                    'price' => 19900
                ],
                [
                    'manufacturer' => 'Asus',
                    'model' => 'MeMO Pad HD 7',
                    'price' => 3110
                ],
                [
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
        // TODO: add test data
        $response = $this->client->request('GET', 'http://webserver/api/v1/tablets/2');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing(
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
        $uuid = Uuid::v4();
        // TODO: add test data
        $response = $this->client->request('GET', "http://webserver/api/v1/tablets/{$uuid}");

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));
    }
}
