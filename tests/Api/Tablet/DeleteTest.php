<?php

namespace Tests\Api\Tablet;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $client->jsonRequest(Request::METHOD_DELETE, "http://webserver/api/tablets/$itemId");

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $this->assertNull($tablet);
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithMissingId(): void
    {
        $client = $this->createClient();
        $client->request(Request::METHOD_DELETE, 'http://webserver/api/tablets/');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithInvalidId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest(Request::METHOD_DELETE, 'http://webserver/api/tablets/abcdefg');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithUnknownId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            'http://webserver/api/tablets/649b05de-00b4-4fb7-8d64-113c1806c9a7'
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
