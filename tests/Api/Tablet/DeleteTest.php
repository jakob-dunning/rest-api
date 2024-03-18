<?php

namespace Tests\Api\Tablet;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \App\Controller\TabletApiController::delete
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 */
class DeleteTest extends WebTestCase
{
    public function testDeleteItem(): void
    {
        $client = $this->createClient();
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';
        $client->jsonRequest('DELETE', "http://webserver/api/tablets/$itemId");

        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $this->assertNull($tablet);
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithMissingId(): void
    {
        $client = $this->createClient();
        $client->request('DELETE', 'http://webserver/api/tablets/');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithInvalidId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest('DELETE', 'http://webserver/api/tablets/abcdefg');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithUnknownId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest('DELETE', 'http://webserver/api/tablets/649b05de-00b4-4fb7-8d64-113c1806c9a7');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
