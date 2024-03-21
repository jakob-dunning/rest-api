<?php

namespace Tests\Api\ShoppingCart;

use App\Entity\ShoppingCart;
use App\Entity\Tablet;
use App\Repository\ShoppingCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

/**
 * @covers \App\Controller\ShoppingCartApiController::create
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 */
class PostTest extends WebTestCase
{
    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\Entity\ShoppingCart
     */
    public function testCreateShoppingCart(): void
    {
        $client = $this->createClient();
        $shoppingCartId = Uuid::v4()->toRfc4122();
        $newShoppingCart =
            [
                'id' => $shoppingCartId,
                'expiresAt' => (new \DateTime())->modify('+1 hour')->format(DATE_ATOM),
                'tablets' => []
            ];
        $client->jsonRequest(
            Request::METHOD_POST,
            'http://webserver/api/shoppingcarts',
            $newShoppingCart
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            ['data' => "http://localhost/api/shoppingcarts/$shoppingCartId"],
            json_decode($client->getResponse()->getContent(), true)
        );

        /* @var ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);

        $this->assertEquals($newShoppingCart, $shoppingCart->toScalarArray());
    }

    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     */
    public function testCreateShoppingCartFailsWithDateInThePast(): void
    {
        $client = $this->createClient();
        $shoppingCartId = Uuid::v4();
        $newShoppingCart =
            [
                'id' => $shoppingCartId,
                'expiresAt' => (new \DateTime())->modify('-1 hour')->format(DATE_ATOM)
            ];
        $client->jsonRequest(
            Request::METHOD_POST,
            'http://webserver/api/shoppingcarts',
            $newShoppingCart
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);

        $this->assertNull($shoppingCart);
    }

    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\Entity\ShoppingCart
     * @covers \App\Controller\ShoppingCartApiController::addTablet
     */
    public function testAddTabletToShoppingCart(): void
    {
        $client = $this->createClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $tabletId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => $tabletId]
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            ['data' => "http://localhost/api/shoppingcarts/$shoppingCartId/tablets/$tabletId"],
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

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddTabletToShoppingCartFailsWithMissingTabletId(): void
    {
        $client = $this->createClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddTabletToShoppingCartFailsWithEmptyId(): void
    {
        $client = $this->createClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => '']
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddTabletToShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->createClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => 'abcde']
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddTabletToShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->createClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shoppingcarts/$shoppingCartId/tablets",
            ['id' => '3090534b-ac67-4d31-8dcc-9458210cb20a']
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
