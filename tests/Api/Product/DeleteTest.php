<?php

namespace Tests\Api\Product;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\Controller\V1\ProductApiController::delete
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\ProductRepository
 */
class DeleteTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    public function testDeleteProduct(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '0bdea651-825f-4648-9cac-4b03f8f4576e';
        $client->jsonRequest(Request::METHOD_DELETE, "http://webserver/api/products/v1/$productId");

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertNull($product);
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testDeleteProductFailsWithMissingId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_DELETE, 'http://webserver/api/products/v1/');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testDeleteProductFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(Request::METHOD_DELETE, 'http://webserver/api/products/v1/abcdefg');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testDeleteProductFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            'http://webserver/api/products/v1/649b05de-00b4-4fb7-8d64-113c1806c9a7'
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
