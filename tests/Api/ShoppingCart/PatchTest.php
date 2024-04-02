<?php

namespace Tests\Api\ShoppingCart;

use App\Entity\ShoppingCart;
use App\Repository\ShoppingCartRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\Controller\V1\ShoppingCartApiController::update
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 */
class PatchTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    /**
     * @covers \App\Entity\ShoppingCart
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\Repository\ShoppingCartRepository
     */
    public function testUpdateExpiresAt(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $expiresAt = (new \DateTime())->modify('+1 hour')->format(DATE_ATOM);
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                'expiresAt' => $expiresAt,
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'data' => [
                    'id' => $shoppingCartId,
                    'expiresAt' => $expiresAt,
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
                    ]
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals($expiresAt, $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    public function testUpdateFailsWithMalformedJson(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $expiresAt = (new \DateTime())->modify('+1 hour')->format(DATE_ATOM);
        $client->request(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            "[{'expiresAt':'$expiresAt'}]",
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);

        $this->assertEquals('2024-03-17T12:44:00+00:00', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     */
    public function testUpdateExpiresAtFailsWithDateInThePast(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $expiresAt = (new \DateTime())->format(DATE_ATOM);
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                'expiresAt' => $expiresAt,
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00+00:00', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    public function testUpdateExpiresAtFailsWithEmptyDate(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                'expiresAt' => '',
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00+00:00', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    /**
     * @covers \App\Dto\ShoppingCartDto
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     */
    public function testUpdateExpiresAtFailsWithDateFormatNotIso8601(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $expiresAt = (new \DateTime())->format(DATE_RFC1036);
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                'expiresAt' => $expiresAt,
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00+00:00', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    public function testUpdateIdFails(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                'id' => Uuid::v4(),
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals($shoppingCartId, $shoppingCart->getId()->toRfc4122());
    }
}
