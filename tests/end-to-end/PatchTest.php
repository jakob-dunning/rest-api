<?php

namespace Tests\EndToEnd;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PatchTest extends \PHPUnit\Framework\TestCase
{
    private HttpClientInterface $client;


    public function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    /**
     * @dataProvider dataFieldProvider
     */
    public function testPatchModifiesDataField(string $dataFieldName, string $dataFieldValue)
    {
        $manufacturer = 'Xiaomi';
        $model = 'Redmi Pad SE';
        $price = 17999;
        $uuid = Uuid::v4();
        // TODO: Add test data

        $response = $this->client->request('PATCH', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => [
                $dataFieldName => $dataFieldValue,
            ]
        ]);

        $expectedItem = [
            'id' => $uuid,
            'manufacturer' => $manufacturer,
            'model' => $model,
            'price' => $price,
        ];
        $expectedItem[$dataFieldName] = $dataFieldValue;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            $expectedItem
        ], json_decode($response->getContent()));
    }

    public function dataFieldProvider()
    {
        return [
            ['manufacturer', 'Asus'],
            ['model', 'MeMO Pad HD 7'],
            ['price', 99999]
        ];
    }

    public function testPatchFailsWithMissingId()
    {
        $response = $this->client->request('PATCH', 'http://webserver/api/v1/tablets', [
            'json' => [
                'manufacturer' => 'Asus',
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Missing id'], json_decode($response->getContent()));
    }

    /**
     * @dataProvider emptyDataFieldProvider
     */
    public function testPatchFailsOnEmptyDataField(string $dataFieldName, string $dataFieldValue)
    {
        $uuid = Uuid::v4();
        // TODO: Add test data

        $response = $this->client->request('PATCH', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => [
                $dataFieldName => $dataFieldValue,
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['error' => sprintf('%s cannot be empty', ucfirst($dataFieldName))],
            json_decode($response->getContent())
        );
    }

    public function emptyDataFieldProvider(): array
    {
        return [
            ['manufacturer', ''],
            ['model', ''],
            ['price', '']
        ];
    }

    public function testPatchModifiesMultipleDataFields()
    {
        $manufacturer = 'Xiaomi';
        $model = 'Redmi Pad SE';
        $price = 17999;
        $uuid = Uuid::v4();
        // TODO: Add test data

        $newManufacturer = 'Asus';
        $newModel = 'MeMO Pad HD 7';

        $response = $this->client->request('PATCH', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => [
                'menufacturer' => $newManufacturer,
                'model' => $newModel,
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            [
                'id' => $uuid,
                'manufacturer' => $newManufacturer,
                'model' => $newModel,
                'price' => $price,
            ]
        ], json_decode($response->getContent()));
    }

    public function testPatchFailsWithMultipleDatafieldsAndAtLeastOneEmptyField()
    {
        $manufacturer = 'Xiaomi';
        $model = 'Redmi Pad SE';
        $price = 17999;
        $uuid = Uuid::v4();
        // TODO: Add test data

        $newManufacturer = 'Asus';
        $newModel = '';

        $response = $this->client->request('PATCH', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => [
                'menufacturer' => $newManufacturer,
                'model' => $newModel,
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Fields cannot be empty: Model'], json_decode($response->getContent()));
    }
}
