<?php

namespace Tests\Api\ShoppingCart;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\Controller\V1\ShoppingCartApiController::show
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\ShoppingCartRepository
 */
class GetTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    /**
     * @covers \App\Entity\ShoppingCart::__construct
     */
    public function testShowShoppingCart(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            'GET',
            "http://webserver/api/shopping-carts/v1/$shoppingCartId"
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'data' => [
                    'id' => $shoppingCartId,
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
                        ]
                    ]
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testShowShoppingCartFailsWithMissingId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            'GET',
            'http://webserver/api/shopping-carts/v1/'
        );

        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testShowShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            'GET',
            'http://webserver/api/shopping-carts/v1/abcde'
        );

        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /** @covers \App\EventSubscriber\HttpNotFoundEventSubscriber */
    public function testShowShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '47eaaaa1-4fde-4c91-a426-9064dd79a354';
        $client->jsonRequest(
            'GET',
            "http://webserver/api/shopping-carts/v1/$shoppingCartId"
        );

        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
