<?php

namespace Api\Product;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\Controller\V1\ProductApiController::create
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\ProductRepository
 */
class PostTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    /**
     * @covers \App\Entity\Product
     * @covers \App\Dto\ProductDto
     */
    public function testCreateNewProduct(): void
    {
        $client = $this->createAuthenticatedClient();
        $newProductId = Uuid::v4();
        $newProduct = [
            'id' => $newProductId->toRfc4122(),
            'type' => 'Tablet',
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 19799
        ];
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/products/v1",
            $newProduct
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertResponseHeaderSame('Location', "http://localhost/api/products/v1/$newProductId");

        $this->assertEquals(['data' => $newProduct], json_decode($client->getResponse()->getContent(), true),);

        $product = $this->getContainer()->get(ProductRepository::class)->find($newProductId);
        $this->assertEquals($newProduct, $product->toScalarArray());
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\ProductDto
     */
    public function testCreateNewProductFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/products/v1",
            [
                'id' => 'abcde',
                'type' => 'tablet',
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => 19799
            ]
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @dataProvider stringPropertyNameProvider
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\ProductDto
     */
    public function testCreateNewProductFailsWithEmptyStringProperty(string $propertyName): void
    {
        $client = $this->createAuthenticatedClient();
        $newProductId = Uuid::v4();
        $newProduct = [
            'id' => $newProductId,
            'type' => 'tablet',
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 19799
        ];
        $newProduct[$propertyName] = '';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/products/v1",
            $newProduct
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($newProductId);
        $this->assertNull($product);
    }

    /**
     * @return array<array<string>>
     */
    public function stringPropertyNameProvider(): array
    {
        return [
            ['manufacturer'],
            ['model'],
            ['type'],
        ];
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\ProductDto
     */
    public function testCreateNewProductFailsWithNegativePrice(): void
    {
        $client = $this->createAuthenticatedClient();
        $newProductId = Uuid::v4();
        $client->jsonRequest(Request::METHOD_POST, "http://webserver/api/products/v1", [
            'id' => $newProductId,
            'type' => 'tablet',
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => -7000
        ]);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($newProductId);
        $this->assertNull($product);
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\ProductDto
     */
    public function testCreateNewProductFailsWithRidiculouslyHighPrice(): void
    {
        $client = $this->createAuthenticatedClient();
        $newProductId = Uuid::v4();
        $client->jsonRequest(Request::METHOD_POST, "http://webserver/api/products/v1", [
            'id' => $newProductId,
            'type' => 'tablet',
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 100000000
        ]);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($newProductId);
        $this->assertNull($product);
    }

    /**
     * @dataProvider propertyNameProvider
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\ProductDto
     */
    public function testCreateNewProductFailsWithMissingProperty(string $propertyName): void
    {
        $client = $this->createAuthenticatedClient();
        $newProductId = Uuid::v4();
        $newProduct = [
            'id' => $newProductId,
            'type' => 'tablet',
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 17999
        ];
        unset($newProduct[$propertyName]);
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/products/v1",
            $newProduct
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($newProductId);
        $this->assertNull($product);
    }

    /**
     * @return array<array<string>>
     */
    public function propertyNameProvider(): array
    {
        return [
            ['manufacturer'],
            ['model'],
            ['price'],
            ['type'],
        ];
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedDeserializationEventSubscriber
     */
    public function testCreateNewProductFailsWithMalformedJson(): void
    {
        $client = $this->createAuthenticatedClient();
        $newProductId = Uuid::v4();
        $client->request(
            Request::METHOD_POST,
            "http://webserver/api/products/v1",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            "{'id':'$newProductId','type':'tablet','manufacturer':'xiaomi','model':'Redmi Note 4','price':9999}"
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $product = $this->getContainer()->get(ProductRepository::class)->find($newProductId);
        $this->assertNull($product);
    }
}
