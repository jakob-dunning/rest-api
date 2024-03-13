<?php

namespace Tests\EndToEnd;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PutTest extends KernelTestCase
{
    private HttpClientInterface $client;
    private TabletRepository $tabletRepository;

    public function __construct()
    {
        parent::__construct();

        self::bootKernel();
        $this->tabletRepository = static::getContainer()->get(TabletRepository::class);
        $this->client = HttpClient::create();
    }

    /**
     * @dataProvider dataFieldProvider
     */
    public function testPutModifiesDataField(string $fieldName, string $fieldValue)
    {
        $itemId = '5c82f07f-3a47-422b-b423-efc3b782ec56';

        $modifiedItem = [
            'id' => $itemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 17999
        ];
        $modifiedItem[$fieldName] = $fieldValue;

        $response = $this->client->request(
            'PUT',
            "http://webserver/api/v1/tablets/$itemId",
            ['json' => $modifiedItem]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([$modifiedItem], json_decode($response->getContent()));

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals($modifiedItem, $tablet->toArray());
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
        $response = $this->client->request(
            'PUT',
            'http://webserver/api/v1/tablets',
            [
                'json' => [
                    'manufacturer' => 'Xiaomi',
                    'model' => 'Redmi Pad SE',
                    'price' => 17999
                ]
            ]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Missing id'], json_decode($response->getContent()));
    }

    /**
     * @dataProvider stringFieldNameProvider
     */
    public function testPutFailsWithEmptyStringField(string $fieldName)
    {
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';

        $modifiedItem = [
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 17999
        ];
        $modifiedItem[$fieldName] = '';

        $response = $this->client->request(
            'PUT',
            "http://webserver/api/v1/tablets/$itemId",
            ['json' => $modifiedItem]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['error' => sprintf('%s cannot be empty', ucfirst($fieldName))],
            json_decode($response->getContent())
        );

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals(
            [
                'id' => $itemId,
                'manufacturer' => 'Lenovo',
                'model' => 'Tab M9',
                'price' => 19900
            ],
            $tablet->toArray()
        );
    }

    public function stringFieldNameProvider(): array
    {
        return [
            ['manufacturer', ''],
            ['model', ''],
        ];
    }

    public function testPutFailsWithNegativePrice(string $fieldName)
    {
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';

        $modifiedItem = [
            'manufacturer' => 'Lenovo',
            'model' => 'Tab M9',
            'price' => -10999
        ];

        $response = $this->client->request(
            'PUT',
            "http://webserver/api/v1/tablets/$itemId",
            ['json' => $modifiedItem]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['error' => 'Price cannot be negative'],
            json_decode($response->getContent())
        );

        $tablet = $this->tabletRepository->find($itemId);

        $this->assertEquals(
            [
                'id' => $itemId,
                'manufacturer' => 'Lenovo',
                'model' => 'Tab M9',
                'price' => 19900
            ],
            $tablet->toArray()
        );
    }

    /**
     * @dataProvider dataFieldNameProvider
     */
    public function testPutFailsWithMissingDataField(string $dataFieldName)
    {
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';

        $modifiedItem = [
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 17999
        ];
        unset($modifiedItem[$dataFieldName]);

        $response = $this->client->request(
            'PUT',
            "http://webserver/api/v1/tablets/$itemId",
            ['json' => $modifiedItem]
        );

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
