<?php

namespace Tests\Api\Tablet;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

/**
 * @covers \App\Controller\TabletApiController::update
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @uses   \App\Entity\Tablet
 */
class PatchTest extends WebTestCase
{
    /**
     * @dataProvider propertyProvider
     * @covers       \App\Dto\TabletDto
     * @covers       \App\Entity\Tablet
     */
    public function testUpdateProperty(string $fieldName, string|int $fieldValue): void
    {
        $client = $this->createClient();
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            [$fieldName => $fieldValue]
        );

        $expectedItem = [
            'id' => $itemId,
            'manufacturer' => 'Asus',
            'model' => 'MeMO Pad HD 7',
            'price' => 3110,
        ];
        $expectedItem[$fieldName] = $fieldValue;

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals(['data' => $expectedItem], json_decode($client->getResponse()->getContent(), true));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);
        $this->assertEquals($expectedItem, $tablet->toScalarArray());
    }

    /**
     * @return array<array<string|int>>
     */
    public function propertyProvider(): array
    {
        return [
            ['manufacturer', 'Lenovo'],
            ['model', 'Tab M9'],
            ['price', 99999]
        ];
    }

    /**
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testUpdatePropertyFailsWithMissingId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            'http://webserver/api/tablets/',
            ['manufacturer' => 'Asus',]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @dataProvider stringPropertyProvider
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\TabletDto
     */
    public function testUpdatePropertyFailsWithEmptyStringProperty(string $fieldName): void
    {
        $client = $this->createClient();
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            [$fieldName => '',]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEquals('', $tablet->toScalarArray()[$fieldName]);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @return array<array<string>>
     */
    public function stringPropertyProvider(): array
    {
        return [
            ['manufacturer'],
            ['model']
        ];
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\TabletDto
     */
    public function testUpdatePropertyFailsWithUnknownProperty(): void
    {
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client = $this->createClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            ['unknownProperty' => '',]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    public function testUpdateIdFails(): void
    {
        $client = $this->createClient();
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            ['id' => Uuid::v4(),]
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        /* @var \App\Entity\Tablet $tablet */
        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($itemId, $tablet->getId());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\TabletDto
     */
    public function testUpdatePriceFailsWithNegativePrice(): void
    {
        $client = $this->createClient();
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            ['price' => -8000,]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $this->assertEquals(3110, $tablet->getPrice());
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\TabletDto
     */
    public function testUpdatePriceFailsWithRidiculouslyHighPrice(): void
    {
        $client = $this->createClient();
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            ['price' => 100000000,]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $this->assertEquals(3110, $tablet->getPrice());
    }

    /**
     * @covers \App\EventSubscriber\JsonFailedDecodingEventSubscriber
     */
    public function testUpdateFailsWithMalformedJson(): void
    {
        $client = $this->createClient();
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';
        $client->request(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            [],
            [],
            ['HTTP_CONTENT_TYPE' => 'application/json'],
            "{'manufacturer':'xiaomi','model':'Redmi Note 4','price':9999}"
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $this->assertEquals('MeMO Pad HD 7', $tablet->getModel());
    }

    /**
     * @covers \App\Dto\TabletDto
     * @covers \App\Entity\Tablet
     */
    public function testUpdateMultipleProperties(): void
    {
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';
        $newManufacturer = 'Acepad';
        $newModel = 'A145TB Flexi';
        $client = $this->createClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            ['manufacturer' => $newManufacturer, 'model' => $newModel,]
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals([
            'data' => [
                'id' => $itemId,
                'manufacturer' => $newManufacturer,
                'model' => $newModel,
                'price' => 19900,
            ]
        ], json_decode($client->getResponse()->getContent(), true));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $this->assertEquals($newManufacturer, $tablet->getManufacturer());
        $this->assertEquals($newModel, $tablet->getModel());
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\TabletDto
     */
    public function testUpdateMultiplePropertiesFailsWithOneEmptyProperty(): void
    {
        $itemId = '0bdea651-825f-4648-9cac-4b03f8f4576e';
        $newManufacturer = 'Asus';
        $newModel = '';
        $client = $this->createClient();
        $client->jsonRequest(
            Request::METHOD_PATCH,
            "http://webserver/api/tablets/$itemId",
            ['manufacturer' => $newManufacturer, 'model' => $newModel,]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($itemId);

        $this->assertEquals('Samsung', $tablet->getManufacturer());
        $this->assertEquals('Galaxy Tab A9+', $tablet->getModel());
    }
}
