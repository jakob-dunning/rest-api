<?php

namespace Tests\Api\ShoppingCart;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetTest extends WebTestCase
{
    public function testShowShoppingCart(): void
    {
        $client = $this->createClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            'GET',
            "http://webserver/api/shoppingcarts/$shoppingCartId"
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                'data' => [
                    'id' => $shoppingCartId,
                    'expiresAt' => '2024-03-17T12:44:00.006Z',
                    'products' => [
                        [
                            'id' => '44682a67-fa83-4216-9e9d-5ea5dd5bf480',
                            'manufacturer' => 'Lenovo',
                            'model' => 'Tab M9',
                            'price' => 19900
                        ],
                        [
                            'id' => '0bdea651-825f-4648-9cac-4b03f8f4576e',
                            'manufacturer' => 'Samsung',
                            'model' => 'Galaxy Tab A9+',
                            'price' => 24799
                        ],
                    ]
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    public function testShowShoppingCartFailsWithMissingId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest(
            'GET',
            'http://webserver/api/shoppingcarts/'
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testShowShoppingCartFailsWithInvalidId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest(
            'GET',
            'http://webserver/api/shoppingcarts/abcde'
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testShowShoppingCartFailsWithUnknownId(): void
    {
        $client = $this->createClient();
        $shoppingCartId = '47eaaaa1-4fde-4c91-a426-9064dd79a354';
        $client->jsonRequest(
            'GET',
            "http://webserver/api/shoppingcarts/$shoppingCartId"
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
