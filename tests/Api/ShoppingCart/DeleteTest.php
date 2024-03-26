<?php

namespace Tests\Api\ShoppingCart;

use App\Entity\Product;
use Tests\Api\AuthenticatedClientTrait;
use App\Repository\ShoppingCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Controller\V1\ShoppingCartApiController::delete
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\ShoppingCartRepository
 */
class DeleteTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    public function testDeleteShoppingCart(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(Request::METHOD_DELETE, "http://webserver/api/shopping-carts/v1/$shoppingCartId");

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEquals('', $client->getResponse()->getContent());

        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertNull($shoppingCart);
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteShoppingCartFailsWithMissingId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(Request::METHOD_DELETE, 'http://webserver/api/shopping-carts/v1/');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(Request::METHOD_DELETE, 'http://webserver/api/shopping-carts/v1/abcde');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testDeleteShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            'http://webserver/api/shopping-carts/v1/49422ab6-a3e7-4440-9066-6ce601251494'
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\Controller\V1\ShoppingCartApiController::removeItem
     */
    public function testRemoveProductFromShoppingCart(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_DELETE,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId/products/$productId"
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $productsWithProductId = array_filter(
            $shoppingCart->getProducts()->toArray(),
            function (Product $product) use ($productId) {
                return $product->getId()->toRfc4122() === $productId;
            }
        );
        $this->assertCount(0, $productsWithProductId);
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testRemoveProductFromShoppingCartFailsWithMissingId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            'http://webserver/api/shopping-carts/v1/5a2dc28e-1282-4e52-b90c-782c908a4e04/products/'
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testRemoveProductFromShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            "http://webserver/api/shopping-carts/v1/5a2dc28e-1282-4e52-b90c-782c908a4e04/products/abcde"
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testRemoveProductFromShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $unknownProductId = '6f36f4c5-5f99-4b97-a908-93a47662e435';
        $client->jsonRequest(
            Request::METHOD_DELETE,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId/products/$unknownProductId"
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
