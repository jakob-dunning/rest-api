<?php

namespace Tests\EndToEnd;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PutTest extends \PHPUnit\Framework\TestCase
{
    private HttpClientInterface $client;


    public function setUp(): void
    {
        $this->client = HttpClient::create();
    }

    /**
     * @dataProvider dataFieldProvider
     */
    public function testPutModifiesDataField(string $dataFieldName, string $dataFieldValue)
    {
        $manufacturer = 'Xiaomi';
        $model = 'Redmi Pad SE';
        $price = 17999;
        $uuid = Uuid::v4();
        // TODO: Add test data

        $modifiedItem = [
            'manufacturer' => $manufacturer,
            'model' => $model,
            'price' => $price
        ];
        $modifiedItem[$dataFieldName] = $dataFieldValue;

        $response = $this->client->request('PUT', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => $modifiedItem
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            $modifiedItem
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

    public function testPutFailsWithMissingId()
    {
        $response = $this->client->request('PATCH', 'http://webserver/api/v1/tablets', [
            'json' => [
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => 17999
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Missing id'], json_decode($response->getContent()));
    }

    /**
     * @dataProvider emptyDataFieldProvider
     */
    public function testPutFailsOnEmptyDataField(string $dataFieldName, string $dataFieldValue)
    {
        $manufacturer = 'Xiaomi';
        $model = 'Redmi Pad SE';
        $price = 17999;
        $uuid = Uuid::v4();
        // TODO: Add test data

        $modifiedItem = [
            'manufacturer' => $manufacturer,
            'model' => $model,
            'price' => $price
        ];

        $response = $this->client->request('PUT', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => $modifiedItem
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

    /**
     * @dataProvider dataFieldNameProvider
     */
    public function testPutFailsOnMissingDataField(string $dataFieldName)
    {
        $manufacturer = 'Xiaomi';
        $model = 'Redmi Pad SE';
        $price = 17999;
        $uuid = Uuid::v4();
        // TODO: Add test data

        $modifiedItem = [
            'manufacturer' => $manufacturer,
            'model' => $model,
            'price' => $price
        ];
        unset($modifiedItem[$dataFieldName]);

        $response = $this->client->request('PUT', "http://webserver/api/v1/tablets/{$uuid}", [
            'json' => $modifiedItem
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['error' => sprintf('Missing fields: %s', ucfirst($dataFieldName))],
            json_decode($response->getContent())
        );
    }

    public function dataFieldNameProvider(): array
    {
        return [
            'manufacturer',
            'model',
            'price',
        ];
    }
}
