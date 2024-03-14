<?php

namespace EndToEnd\TabletApi;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PatchTest extends KernelTestCase
{
    private HttpClientInterface $client;
    private TabletRepository $tabletRepository;

    public function setUp(): void
    {
        self::bootKernel();
        $this->tabletRepository = static::getContainer()->get(TabletRepository::class);
        $this->client = HttpClient::create();
    }

    /**
     * @dataProvider dataFieldProvider
     */
    public function testPatchModifiesDataField(string $fieldName, string|int $fieldValue)
    {
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';

        $response = $this->client->request(
            'PATCH',
            "http://webserver/api/tablets/$itemId",
            ['json' => [$fieldName => $fieldValue,]]
        );

        $expectedItem = [
            'id' => $itemId,
            'manufacturer' => 'Asus',
            'model' => 'MeMO Pad HD 7',
            'price' => 3110,
        ];
        $expectedItem[$fieldName] = $fieldValue;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([$expectedItem], json_decode($response->getContent(), true));

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals($expectedItem, $tablet->toArray());
    }

    public function dataFieldProvider()
    {
        return [
            ['manufacturer', 'Lenovo'],
            ['model', 'Tab M9'],
            ['price', 99999]
        ];
    }

    public function testPatchFailsWithMissingId()
    {
        $response = $this->client->request(
            'PATCH',
            'http://webserver/api/tablets',
            ['json' => ['manufacturer' => 'Asus',]]
        );

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @dataProvider stringFieldNameProvider
     */
    public function testPatchFailsWithEmptyStringField(string $fieldName)
    {
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';

        $response = $this->client->request(
            'PATCH',
            "http://webserver/api/tablets/$itemId",
            ['json' => [$fieldName => '',]]
        );

        $this->assertEquals(400, $response->getStatusCode());

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertNotEquals('', $tablet->toArray()[$fieldName]);
    }

    public function stringFieldNameProvider(): array
    {
        return [
            ['manufacturer'],
            ['model']
        ];
    }

    public function testPatchFailsWithNegativePrice()
    {
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';

        $response = $this->client->request(
            'PATCH',
            "http://webserver/api/tablets/$itemId",
            ['json' => ['price' => -8000,]]
        );

        $this->assertEquals(400, $response->getStatusCode());

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals(3110, $tablet->toArray()['price']);
    }

    public function testPatchFailsWithRidiculouslyHighPrice()
    {
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';

        $response = $this->client->request(
            'PATCH',
            "http://webserver/api/tablets/$itemId",
            ['json' => ['price' => 100000000,]]
        );

        $this->assertEquals(400, $response->getStatusCode());

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals(3110, $tablet->toArray()['price']);
    }

    public function testPatchModifiesMultipleDataFields()
    {
        $itemId = '0bdea651-825f-4648-9cac-4b03f8f4576e';
        $newManufacturer = 'Asus';
        $newModel = 'MeMO Pad HD 7';

        $response = $this->client->request(
            'PATCH',
            "http://webserver/api/tablets/$itemId",
            ['json' => ['manufacturer' => $newManufacturer, 'model' => $newModel,]]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            [
                'id' => $itemId,
                'manufacturer' => $newManufacturer,
                'model' => $newModel,
                'price' => 24799,
            ]
        ], json_decode($response->getContent(), true));

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals($newManufacturer, $tablet->toArray()['manufacturer']);
        $this->assertEquals($newModel, $tablet->toArray()['model']);
    }

    public function testPatchFailsWithMultipleDatafieldsAndAtLeastOneEmptyField()
    {
        $itemId = '0bdea651-825f-4648-9cac-4b03f8f4576e';
        $newManufacturer = 'Asus';
        $newModel = '';

        $response = $this->client->request(
            'PATCH',
            "http://webserver/api/tablets/$itemId",
            ['json' => ['manufacturer' => $newManufacturer, 'model' => $newModel,]]
        );

        $this->assertEquals(400, $response->getStatusCode());

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals('Samsung', $tablet->toArray()['manufacturer']);
        $this->assertEquals('Galaxy Tab A9+', $tablet->toArray()['model']);
    }
}
