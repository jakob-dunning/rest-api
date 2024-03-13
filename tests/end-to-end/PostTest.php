<?php

namespace Tests\EndToEnd;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostTest extends \PHPUnit\Framework\TestCase
{
    private HttpClientInterface $client;

    public function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    public function testPostCreatesNewItem()
    {
        $uuid = Uuid::v4();

        $response = $this->client->request('POST', "http://webserver/api/v1/tablets", [
            'json' => [
                'id' => $uuid,
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => 19799
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(["http://webserver/api/v1/tablets/{$uuid}"], json_decode($response->getContent()));
    }

    public function testPostFailsWithInvalidId()
    {
        $response = $this->client->request('POST', "http://webserver/api/v1/tablets", [
            'json' => [
                'id' => 'abcde',
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => 19799
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Id is invalid uuid v4'], json_decode($response->getContent()));
    }

    public function testPostFailsWithEmptyId()
    {
        $response = $this->client->request('POST', "http://webserver/api/v1/tablets", [
            'json' => [
                'id' => '',
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => 19799
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Id cannot be empty'], json_decode($response->getContent()));
    }

    public function testPostFailsWithEmptyManufacturer()
    {
        $response = $this->client->request('POST', "http://webserver/api/v1/tablets", [
            'json' => [
                'id' => Uuid::v4(),
                'manufacturer' => '',
                'model' => 'Redmi Pad SE',
                'price' => 19799
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Manufacturer cannot be empty'], json_decode($response->getContent()));
    }

    public function testPostFailsWithEmptyModel()
    {
        $response = $this->client->request('POST', "http://webserver/api/v1/tablets", [
            'json' => [
                'id' => Uuid::v4(),
                'manufacturer' => 'Xiaomi',
                'model' => '',
                'price' => 19799
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Model cannot be empty'], json_decode($response->getContent()));
    }

    public function testPostFailsWithNegativePrice()
    {
        $response = $this->client->request('POST', "http://webserver/api/v1/tablets", [
            'json' => [
                'id' => Uuid::v4(),
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => -7
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Price cannot be negative'], json_decode($response->getContent()));
    }

    public function testPostFailsWithEmptyPrice()
    {
        $response = $this->client->request('POST', "http://webserver/api/v1/tablets", [
            'json' => [
                'id' => Uuid::v4(),
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => ''
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Price cannot be empty'], json_decode($response->getContent()));
    }
}
