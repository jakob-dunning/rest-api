<?php

namespace Tests\Api\Tablet;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetTest extends WebTestCase
{
    /**
     * @covers \App\Controller\TabletApiController::list
     * @covers \App\EventSubscriber\JsonResponseEventSubscriber
     */
    public function testShowAllItems(): void
    {
        $client = $this->createClient();
        $client->jsonRequest('GET', 'http://webserver/api/tablets');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEqualsCanonicalizing(
            [
                'data' => [
                    [
                        'id' => '44682a67-fa83-4216-9e9d-5ea5dd5bf480',
                        'manufacturer' => 'Lenovo',
                        'model' => 'Tab M9',
                        'price' => 19900
                    ],
                    [
                        'id' => '5c82f07f-3a47-422b-b423-efc3b782ec56',
                        'manufacturer' => 'Asus',
                        'model' => 'MeMO Pad HD 7',
                        'price' => 3110
                    ],
                    [
                        'id' => '0bdea651-825f-4648-9cac-4b03f8f4576e',
                        'manufacturer' => 'Samsung',
                        'model' => 'Galaxy Tab A9+',
                        'price' => 24799
                    ]
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    /**
     * @covers \App\Controller\TabletApiController::show
     */
    public function testShowSingleItem(): void
    {
        $client = $this->createClient();
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';
        $client->jsonRequest('GET', "http://webserver/api/tablets/$itemId");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'data' => [
                    'id' => $itemId,
                    'manufacturer' => 'Lenovo',
                    'model' => 'Tab M9',
                    'price' => 19900
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    /**
     * @covers \App\Controller\TabletApiController::show
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testShowSingleItemFailsWithInvalidId(): void
    {
        $client = $this->createClient();
        $itemId = 'abcde';
        $client->jsonRequest('GET', "http://webserver/api/tablets/$itemId");

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @covers \App\Controller\TabletApiController::show
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testShowSingleItemFailsWithUnknownId(): void
    {
        $client = $this->createClient();
        $itemId = '66a5c0d8-4289-43ba-941a-e235f722c438';
        $client->jsonRequest('GET', "http://webserver/api/tablets/$itemId");

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
