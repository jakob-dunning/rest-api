<?php

namespace Tests\Api\ShoppingCart;

use App\Entity\Tablet;
use App\Repository\ShoppingCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteTest extends WebTestCase
{
    public function testDeleteShoppingCart(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(Request::METHOD_DELETE, "http://webserver/api/shoppingcarts/$shoppingCartId");

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertFalse($client->getResponse()->getContent());

        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class);

        $this->assertNull($shoppingCart);
    }

    public function testDeleteShoppingCartFailsWithMissingId(): void
    {
        $client = $this->getClient();
        $client->jsonRequest(Request::METHOD_DELETE, 'http://webserver/api/shoppingcarts/');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testDeleteShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->getClient();
        $client->jsonRequest(Request::METHOD_DELETE, 'http://webserver/api/shoppingcarts/abcde');

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testDeleteShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->getClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            'http://webserver/api/shoppingcarts/49422ab6-a3e7-4440-9066-6ce601251494'
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testRemoveTabletFromShoppingCart(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $tabletId = '0bdea651-825f-4648-9cac-4b03f8f4576e';
        $client->jsonRequest(
            Request::METHOD_DELETE,
            "http://webserver/api/cart/$shoppingCartId/products/$tabletId"
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $productsWithTabletId = array_filter(
            $shoppingCart->getTablets()->toArray(),
            function (Tablet $tablet) use ($tabletId) {
                return $tablet->getId()->toRfc4122() === $tabletId;
            }
        );
        $this->assertCount(0, $productsWithTabletId);
    }

    public function testRemoveTabletFromShoppingCartFailsWithMissingTabletId(): void
    {
        $client = $this->getClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            'http://webserver/api/shoppingcarts/5a2dc28e-1282-4e52-b90c-782c908a4e04/products/'
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testRemoveTabletFromShoppingCartFailsWithInvalidTabletId(): void
    {
        $client = $this->getClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            "http://webserver/api/shoppingcarts/5a2dc28e-1282-4e52-b90c-782c908a4e04/products/abcde"
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testRemoveTabletFromShoppingCartFailsWithUnknownTabletId(): void
    {
        $client = $this->getClient();
        $client->jsonRequest(
            Request::METHOD_DELETE,
            "http://webserver/api/shoppingcarts/5a2dc28e-1282-4e52-b90c-782c908a4e04/products/6f36f4c5-5f99-4b97-a908-93a47662e435"
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
