<?php

namespace Tests\Api\ShoppingCart;

use App\Entity\ShoppingCart;
use App\Repository\ShoppingCartRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
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
                [
                    'op' => 'replace',
                    'path' => '/expiresAt',
                    'value' => $expiresAt
                ]
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                'data' => [
                    'id' => $shoppingCartId,
                    'expiresAt' => $expiresAt,
                    'tablets' => [
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

    /**
     * @covers \App\EventSubscriber\JsonFailedDecodingEventSubscriber
     */
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
            "[{'op':'replace', 'path':'/expiresAt', 'value':'$expiresAt'}]",
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
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
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
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
                [
                    'op' => 'replace',
                    'path' => '/expiresAt',
                    'value' => $expiresAt
                ]
            ]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00+00:00', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    /**
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
     */
    public function testUpdateExpiresAtFailsWithEmptyDate(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                [
                    'op' => 'replace',
                    'path' => '/expiresAt',
                    'value' => ''
                ]
            ]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
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
                [
                    'op' => 'replace',
                    'path' => '/expiresAt',
                    'value' => $expiresAt
                ]
            ]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals('2024-03-17T12:44:00+00:00', $shoppingCart->getExpiresAt()->format(DATE_ATOM));
    }

    /**
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
     */
    public function testUpdateIdFails(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                [
                    'op' => 'replace',
                    'path' => '/id',
                    'value' => $shoppingCartId
                ]
            ]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);
        $this->assertEquals($shoppingCartId, $shoppingCart->getId()->toRfc4122());
    }

    /**
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
     */
    public function testUpdatePropertyFailsWithOperationAdd(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                [
                    'op' => 'add',
                    'path' => "/newProperty",
                    'value' => 'xyz'
                ]
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
     */
    public function testUpdatePropertyFailsWithOperationRemove(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                [
                    'op' => 'remove',
                    'path' => "/expiresAt",
                ]
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        /* @var ShoppingCart $shoppingCart */
        $shoppingCart = $this->getContainer()->get(ShoppingCartRepository::class)->find($shoppingCartId);

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(new \DateTime('2024-03-17T12:44:00+00:00'), $shoppingCart->getExpiresAt());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
     */
    public function testUpdatePropertyFailsWithOperationMove(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                [
                    'op' => 'move',
                    'from' => '/expiresAt',
                    'path' => '/validUntil',
                ]
            ]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver
     * @covers \App\Dto\ShoppingCartPatchDto
     * @covers \App\Dto\ShoppingCartPatchDtoList
     */
    public function testUpdatePropertyFailsWithOperationCopy(): void
    {
        $client = $this->createAuthenticatedClient();
        $shoppingCartId = '5a2dc28e-1282-4e52-b90c-782c908a4e04';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/shopping-carts/v1/$shoppingCartId",
            [
                [
                    'op' => 'move',
                    'from' => '/expiresAt',
                    'path' => "/expiresAt2",
                ]
            ]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
