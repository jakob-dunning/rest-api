<?php

namespace Tests\Api\Tablet;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\Controller\V1\TabletApiController::delete
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\TabletRepository
 */
class DeleteTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    public function testDeleteItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $tabletId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';
        $client->jsonRequest(Request::METHOD_DELETE, "http://webserver/api/tablets/v1/$tabletId");

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($tabletId);

        $this->assertNull($tablet);
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithMissingId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_DELETE, 'http://webserver/api/tablets/v1/');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(Request::METHOD_DELETE, 'http://webserver/api/tablets/v1/abcdefg');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteItemFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            'http://webserver/api/tablets/v1/649b05de-00b4-4fb7-8d64-113c1806c9a7'
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
