<?php

namespace EndToEnd\TabletApi;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeleteTest extends KernelTestCase
{
    private HttpClientInterface $client;
    private TabletRepository $tabletRepository;

    public function setUp(): void
    {
        self::bootKernel();
        $this->tabletRepository = static::getContainer()->get(TabletRepository::class);
        $this->client = HttpClient::create();
    }

    public function testDeleteItem()
    {
        $id = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';

        $response = $this->client->request('DELETE', "http://webserver/api/tablets/$id");

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals([], json_decode($response->getContent(), true));

        $tablet = $this->tabletRepository->find($id);

        $this->assertNull($tablet);
    }

    public function testDeleteItemFailsWithMissingId()
    {
        $response = $this->client->request('DELETE', 'http://webserver/api/tablets');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Missing id'], json_decode($response->getContent(), true));
    }
}
