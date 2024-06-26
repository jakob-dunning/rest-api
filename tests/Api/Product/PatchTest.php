<?php

namespace Tests\Api\Product;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\Controller\V1\ProductApiController::update
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\ProductRepository
 * @uses   \App\Entity\Product
 */
class PatchTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    /**
     * @dataProvider propertyProvider
     * @covers       \App\Dto\ProductDto
     * @covers       \App\Entity\Product
     */
    public function testUpdateProperty(string $propertyName, string|int $propertyValue): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                $propertyName => $propertyValue,
            ]
        );

        $expectedProduct = [
            'id' => $productId,
            'type' => 'Tablet',
            'manufacturer' => 'Asus',
            'model' => 'MeMO Pad HD 7',
            'price' => 3110,
        ];
        $expectedProduct[$propertyName] = $propertyValue;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals(['data' => $expectedProduct], json_decode($client->getResponse()->getContent(), true));

        /* @var Product $product */
        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertEquals($expectedProduct, $product->toScalarArray());
    }

    /**
     * @return array<array<string|int>>
     */
    public function propertyProvider(): array
    {
        return [
            ['manufacturer', 'Lenovo'],
            ['model', 'Tab M9'],
            ['price', 99999],
        ];
    }

    /**
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testUpdatePropertyFailsWithMissingId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            'http://webserver/api/products/v1/',
            [
                'manufacturer' => 'Asus',
            ]
        );

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @dataProvider stringPropertyProvider
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\ProductDto
     */
    public function testUpdatePropertyFailsWithEmptyStringProperty(string $propertyName): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                $propertyName => '',
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var Product $product */
        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertNotEquals('', $product->toArray()[$propertyName]);
    }

    /**
     * @return array<array<string>>
     */
    public function stringPropertyProvider(): array
    {
        return [
            ['manufacturer'],
            ['model'],
            ['type']
        ];
    }

    /**
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\ProductDto
     */
    public function testUpdatePropertyFailsWithUnknownProperty(): void
    {
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                'unknownProperty' => 'xyz',
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testUpdateIdFails(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                'id' => Uuid::v4(),
            ]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        /* @var \App\Entity\Product $product */
        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertEquals($productId, $product->getId());
    }

    /**
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\ProductDto
     */
    public function testUpdatePriceFailsWithNegativePrice(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                'price' => -8000,
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertEquals(3110, $product->getPrice());
    }

    /**
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\ProductDto
     */
    public function testUpdatePriceFailsWithRidiculouslyHighPrice(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                'price' => 100000000,
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertEquals(3110, $product->getPrice());
    }

    public function testUpdateFailsWithMalformedJson(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->request(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            "[{'price': 999}]",
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertEquals('MeMO Pad HD 7', $product->getModel());
    }

    /**
     * @covers       \App\Dto\ProductDto
     * @covers       \App\Entity\Product
     */
    public function testUpdateMultipleProperties(): void
    {
        $productId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';
        $newManufacturer = 'Acepad';
        $newModel = 'A145TB Flexi';
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                'manufacturer' => $newManufacturer,
                'model' => $newModel,
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals([
            'data' => [
                'id' => $productId,
                'type' => 'Tablet',
                'manufacturer' => $newManufacturer,
                'model' => $newModel,
                'price' => 19900,
            ]
        ], json_decode($client->getResponse()->getContent(), true));

        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertEquals($newManufacturer, $product->getManufacturer());
        $this->assertEquals($newModel, $product->getModel());
    }

    /**
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\ProductDto
     */
    public function testUpdateMultiplePropertiesFailsWithOneEmptyProperty(): void
    {
        $productId = '0bdea651-825f-4648-9cac-4b03f8f4576e';
        $newManufacturer = 'Asus';
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/products/v1/$productId",
            [
                    'manufacturer' => $newManufacturer,
                    'model' => '',
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($productId);
        $this->assertEquals('Samsung', $product->getManufacturer());
        $this->assertEquals('Galaxy Tab A9+', $product->getModel());
    }
}
