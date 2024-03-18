<?php

namespace Tests\Api\ShoppingCart;

use App\Entity\Tablet;
use App\Repository\ShoppingCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostTest extends WebTestCase
{
    public function testAddTabletToShoppingCart(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $tabletId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => $tabletId]
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            ['data' => "http://localhost/api/carts/$shoppingCartId/tablets/$tabletId"],
            json_decode($client->getResponse()->getContent(), true)
        );

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $productsWithTabletId = array_filter(
            $shoppingCart->getTablets()->toArray(),
            function (Tablet $tablet) use ($tabletId) {
                return $tablet->getId()->toRfc4122() === $tabletId;
            }
        );
        $this->assertCount(1, $productsWithTabletId);
    }

    public function testAddTabletToShoppingCartFailsWithMissingTabletId(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testAddTabletToShoppingCartFailsWithEmptyId(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => '']
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testAddTabletToShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => 'abcde']
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testAddTabletToShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => '3090534b-ac67-4d31-8dcc-9458210cb20a']
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
