<?php

namespace Tests\EndToEnd;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
    private HttpClientInterface $client;

    public function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    public function testDeleteItem()
    {
        $uuid = Uuid::v4();
        // Todo: Add test data

        $response = $this->client->request('DELETE', "http://webserver/api/v1/tablets/{$uuid}");

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent()));
    }

    public function testDeleteItemFailsWithMissingId()
    {
        $response = $this->client->request('DELETE', 'http://webserver/api/v1/tablets');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Missing id'], json_decode($response->getContent()));
    }
}
