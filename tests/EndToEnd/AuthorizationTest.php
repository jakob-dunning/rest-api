<?php

namespace Tests\EndToEnd;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthorizationTest extends \PHPUnit\Framework\TestCase
{
    private HttpClientInterface $client;
    public function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    public function testGetFailsWithoutAuthorization()
    {
        // TODO: add test data
        $response = $this->client->request('GET', 'http://webserver/api/v1/tablets');

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));
    }

    public function testPostFailsWithoutAuthorization()
    {
        $response = $this->client->request('POST', 'http://webserver/api/v1/tablets', [
            'json' => [
                'id' => Uuid::v4(),
                'manufacturer' => 'Apple',
                'model' => 'iPad Wi-Fi (9th generation 2021)',
                'price' => 36900
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));

        // TODO: Check if database is empty
    }

    public function testPutFailsWithoutAuthorization()
    {
        $uuid = Uuid::v4();
        // TODO: Add test data
        $response = $this->client->request('PUT', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => [
                'manufacturer' => 'Apple',
                'model' => 'iPad Wi-Fi (9th generation 2021)',
                'price' => 39900
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));

        // TODO: Check if database has changed
    }

    public function testPatchFailsWithoutAuthorization()
    {
        $uuid = Uuid::v4();
        // TODO: Add test data
        $response = $this->client->request('PATCH', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => [
                'price' => 38900
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));

        // TODO: Check if database has changed
    }

    public function testDeleteFailsWithoutAuthorization()
    {
        $uuid = Uuid::v4();
        // TODO: Add test data
        $response = $this->client->request('DELETE', "http://webserver/api/v1/tablets/{$uuid}");

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));

        // TODO: Check if database has changed
    }
}
