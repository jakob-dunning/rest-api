<?php

namespace Tests\Api\ShoppingCart;

use App\Repository\ShoppingCartRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PatchTest extends WebTestCase
{
    public function testUpdateExpiresAt(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $expiresAt = (new \DateTime())->format(DATE_ATOM);
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shoppingcarts/$shoppingCartId",
            ['expiresAt' => $expiresAt]
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                'data' => [
                    'id' => $shoppingCartId,
                    'expiresAt' => $expiresAt,
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

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals($expiresAt, $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    public function testUpdateExpiresAtFailsWithDateInThePast(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $expiresAt = (new \DateTime())->format(DATE_ATOM);
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shoppingcarts/$shoppingCartId",
            ['expiresAt' => $expiresAt]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00.006Z', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    public function testUpdateExpiresAtFailsWithEmptyDate(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shoppingcarts/$shoppingCartId",
            ['expiresAt' => '']
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00.006Z', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    public function testUpdateExpiresAtFailsWithDateFormatNotIso8601(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $expiresAt = (new \DateTime())->format(DATE_RFC1036);
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shoppingcarts/$shoppingCartId",
            ['expiresAt' => $expiresAt]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00.006Z', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    public function testUpdateIdFails(): void
    {
        $client = $this->getClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shoppingcarts/$shoppingCartId",
            ['id' => $shoppingCartId]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals($shoppingCartId, $shoppingCart->getId()->toRfc4122());
    }
}