<?php

namespace EndToEnd\TabletApi;

use App\Repository\TabletRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostTest extends KernelTestCase
{
    private HttpClientInterface $client;
    private TabletRepository $tabletRepository;

    public function setUp(): void
    {
        self::bootKernel();
        $this->tabletRepository = static::getContainer()->get(TabletRepository::class);
        $this->client = HttpClient::create();
    }

    public function testPostCreatesNewItem()
    {
        $newItemId = Uuid::v4();
        $newItem = [
            'id' => $newItemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 19799
        ];

        $response = $this->client->request(
            'POST',
            "http://webserver/api/tablets",
            ['json' => $newItem]
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(["http://webserver/api/tablets/$newItemId"], json_decode($response->getContent(), true));

        $tablet = $this->tabletRepository->find($newItemId);

        $this->assertEquals($newItem, $tablet->toArray());
    }

    public function testPostFailsWithInvalidId()
    {
        $response = $this->client->request(
            'POST',
            "http://webserver/api/tablets",
            [
                'json' => [
                    'id' => 'abcde',
                    'manufacturer' => 'Xiaomi',
                    'model' => 'Redmi Pad SE',
                    'price' => 19799
                ]
            ]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Id is invalid uuid v4'], json_decode($response->getContent(), true));
    }

    /**
     * @dataProvider stringFieldNameProvider
     */
    public function testPostFailsWithEmptyStringField(string $fieldName)
    {
        $newItemId = Uuid::v4();
        $newItem = [
            'id' => $newItemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 19799
        ];
        $newItem[$fieldName] = '';

        $response = $this->client->request(
            'POST',
            "http://webserver/api/tablets",
            ['json' => $newItem]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['error' => sprintf('%s cannot be empty', ucfirst($fieldName))],
            json_decode($response->getContent(), true)
        );

        $tablet = $this->tabletRepository->find($newItemId);

        $this->assertNull($tablet);
    }

    public function stringFieldNameProvider(): array
    {
        return [
            ['manufacturer', ''],
            ['model', ''],
        ];
    }

    public function testPostFailsWithNegativePrice()
    {
        $newItemId = Uuid::v4();

        $response = $this->client->request('POST', "http://webserver/api/tablets", [
            'json' => [
                'id' => $newItemId,
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => -7000
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Price cannot be negative'], json_decode($response->getContent(), true));

        $tablet = $this->tabletRepository->find($newItemId);

        $this->assertNull($tablet);
    }

    public function testPostFailsWithRidiculouslyHighPrice()
    {
        $newItemId = Uuid::v4();

        $response = $this->client->request('POST', "http://webserver/api/tablets", [
            'json' => [
                'id' => $newItemId,
                'manufacturer' => 'Xiaomi',
                'model' => 'Redmi Pad SE',
                'price' => 100000000
            ]
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => 'Price cannot be higher than 100000000'], json_decode($response->getContent(), true));

        $tablet = $this->tabletRepository->find($newItemId);

        $this->assertNull($tablet);
    }

    /**
     * @dataProvider dataFieldNameProvider
     */
    public function testPostFailsWithMissingDataField(string $dataFieldName)
    {
        $newItemId = Uuid::v4();

        $newItem = [
            'id' => $newItemId,
            'manufacturer' => 'Xiaomi',
            'model' => 'Redmi Pad SE',
            'price' => 17999
        ];
        unset($newItem[$dataFieldName]);

        $response = $this->client->request(
            'POST',
            "http://webserver/api/tablets",
            ['json' => $newItem]
        );

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            ['error' => sprintf('Missing fields: %s', ucfirst($dataFieldName))],
            json_decode($response->getContent(), true)
        );

        $tablet = $this->tabletRepository->find($newItemId);

        $this->assertNull($tablet);
    }

    public function dataFieldNameProvider(): array
    {
        return [
            ['manufacturer'],
            ['model'],
            ['price'],
        ];
    }
}
