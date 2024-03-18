<?php

namespace Api\Tablet;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

/**
 * @covers \App\Controller\TabletApiController::create
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 */
class PostTest extends WebTestCase
{
    /**
     * @covers \App\Entity\Tablet
     * @covers \App\Dto\TabletDto
     */
    public function testCreateNewItem(): void
    {
        $client = $this->createClient();
        $newItemId = Uuid::v4();
        $newItem = [
            'id' => $newItemId->toRfc4122(),
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 19799
        ];
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/tablets",
            $newItem
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            ['data' => "http://localhost/api/tablets/$newItemId"],
            json_decode($client->getResponse()->getContent(), true)
        );

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($newItemId);

        $this->assertEquals($newItem, $tablet->toScalarArray());
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\TabletDto
     */
    public function testCreateNewItemFailsWithInvalidId(): void
    {
        $client = $this->createClient();
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/tablets",
            [
                'id' => 'abcde',
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => 19799
            ]
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @dataProvider stringPropertyNameProvider
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\TabletDto
     */
    public function testCreateNewItemFailsWithEmptyStringProperty(string $propertyName): void
    {
        $client = $this->createClient();
        $newItemId = Uuid::v4();
        $newItem = [
            'id' => $newItemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 19799
        ];
        $newItem[$propertyName] = '';
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/tablets",
            $newItem
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($newItemId);

        $this->assertNull($tablet);
    }

    /**
     * @return array<array<string>>
     */
    public function stringPropertyNameProvider(): array
    {
        return [
            ['manufacturer'],
            ['model'],
        ];
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\TabletDto
     */
    public function testCreateNewItemFailsWithNegativePrice(): void
    {
        $client = $this->createClient();
        $newItemId = Uuid::v4();
        $client->jsonRequest(Request::METHOD_POST, "http://webserver/api/tablets", [
            'id' => $newItemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => -7000
        ]);

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($newItemId);

        $this->assertNull($tablet);
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers \App\Dto\TabletDto
     */
    public function testCreateNewItemFailsWithRidiculouslyHighPrice(): void
    {
        $client = $this->createClient();
        $newItemId = Uuid::v4();
        $client->jsonRequest(Request::METHOD_POST, "http://webserver/api/tablets", [
            'id' => $newItemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 100000000
        ]);

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($newItemId);

        $this->assertNull($tablet);
    }

    /**
     * @dataProvider propertyNameProvider
     * @covers       \App\EventSubscriber\PayloadFailedValidationEventSubscriber
     * @covers       \App\Dto\TabletDto
     */
    public function testCreateNewItemFailsWithMissingProperty(string $propertyName): void
    {
        $client = $this->createClient();
        $newItemId = Uuid::v4();
        $newItem = [
            'id' => $newItemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 17999
        ];
        unset($newItem[$propertyName]);
        $client->jsonRequest(
            Request::METHOD_POST,
            "http://webserver/api/tablets",
            $newItem
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($newItemId);

        $this->assertNull($tablet);
    }

    /**
     * @covers \App\EventSubscriber\PayloadFailedDeserializationEventSubscriber
     */
    public function testCreateNewItemFailsWithMalformedJson(): void
    {
        $client = $this->createClient();
        $newItemId = Uuid::v4();
        $client->request(
            Request::METHOD_POST,
            "http://webserver/api/tablets",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            "{'id':'$newItemId','manufacturer':'xiaomi','model':'Redmi Note 4','price':9999}"
        );

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));

        $tablet = $this->getContainer()->get(TabletRepository::class)->find($newItemId);

        $this->assertNull($tablet);
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
        ];
    }
}
