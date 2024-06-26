<?php

namespace Tests\Api\ShoppingCart;

use App\Entity\ShoppingCart;
use App\Entity\Product;
use App\Repository\ShoppingCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\Controller\V1\ShoppingCartApiController::create
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\ShoppingCartRepository
 */
class PostTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\Entity\ShoppingCart
     */
    public function testCreateShoppingCart(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = Uuid::v4()->toRfc4122();
        $expiresAt = (new \DateTime())->modify('+1 hour')->format(DATE_ATOM);
        $client->jsonRequest(
            Request::METHOD_POST,
            'http://webserver/api/shopping-carts/v1',
            [
                'id' => $shoppingCartId,
                'expiresAt' => $expiresAt,
            ]
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'data' => [
                    'id' => $shoppingCartId,
                    'expiresAt' => $expiresAt,
                    'products' => []
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
        $this->assertResponseHeaderSame(
            'Location',
            "http://localhost/api/shopping-carts/v1/$shoppingCartId"
        );

        /* @var ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals([
            'id' => $shoppingCartId,
            'expiresAt' => $expiresAt,
            'products' => []
        ], $shoppingCart->toScalarArray());
    }

    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     */
    public function testCreateShoppingCartFailsWithDateInThePast(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = Uuid::v4();
        $client->jsonRequest(
            Request::METHOD_POST,
            'http://webserver/api/shopping-carts/v1',
            [
                'id' => $shoppingCartId,
                'expiresAt' => (new \DateTime())->modify('-1 hour')->format(DATE_ATOM)
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);

        $this->assertNull($shoppingCart);
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedDeserializationEventSubscriber
     */
    public function testCreateShoppingCartFailsWithMalformedJson(): void
    {
        $client = $this->createAuthenticatedClient();
        $newShoppingCartId = Uuid::v4();
        $expiresAt = (new \DateTime())->modify('+1 hour')->format(DATE_ATOM);
        $client->request(
            Request::METHOD_POST,
            "http://webserver/api/shopping-carts/v1",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            "{'id':'$newShoppingCartId','expiresAt':'$expiresAt'}"
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($newShoppingCartId);
        $this->assertNull($shoppingCart);
    }

    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\Entity\ShoppingCart
     * @covers \App\Controller\V1\ShoppingCartApiController::addItem
     */
    public function testAddProductToShoppingCart(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $productId = '0bdea651-825f-4648-9cac-4b03f8f4576e';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId/products",
            ['id' => $productId]
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'data' => [
                    'id' => '5a2dc28e-1282-4e52-b90c-782c908a4e04',
                    'expiresAt' => '2024-03-17T12:44:00+00:00',
                    'products' => [
                        [
                            'id' => '44682a67-fa83-4216-9e9d-5ea5dd5bf480',
                            'type' => 'Tablet',
                            'manufacturer' => 'Lenovo',
                            'model' => 'Tab M9',
                            'price' => 19900
                        ],
                        [
                            'id' => '5c82f07f-3a47-422b-b423-efc3b782ec56',
                            'type' => 'Tablet',
                            'manufacturer' => 'Asus',
                            'model' => 'MeMO Pad HD 7',
                            'price' => 3110
                        ],
                        [
                            'id' => '0bdea651-825f-4648-9cac-4b03f8f4576e',
                            'type' => 'Tablet',
                            'manufacturer' => 'Samsung',
                            'model' => 'Galaxy Tab A9+',
                            'price' => 24799
                        ]
                    ]
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
        $this->assertResponseHeaderSame(
            'Location',
            "http://localhost/api/shopping-carts/v1/$shoppingCartId/products/$productId"
        );

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $productsWithProductId = array_filter(
            $shoppingCart->getProducts()->toArray(),
            function (Product $product) use ($productId) {
                return $product->getId()->toRfc4122() === $productId;
            }
        );
        $this->assertCount(1, $productsWithProductId);
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddProductToShoppingCartFailsWithMissingProductId(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId/products",
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddTabletToShoppingCartFailsWithEmptyId(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId/products",
            ['id' => '']
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddTabletToShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId/products",
            ['id' => 'abcde']
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testAddTabletToShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId/products",
            ['id' => '3090534b-ac67-4d31-8dcc-9458210cb20a']
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
