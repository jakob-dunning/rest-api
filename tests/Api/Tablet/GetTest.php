<?php

namespace Tests\Api\Tablet;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\AuthenticatedClientTrait;

class GetTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    /**
     * @covers \App\Controller\V1\TabletApiController::list
     * @covers \App\EventSubscriber\JsonResponseEventSubscriber
     * @covers \App\Entity\Tablet::__construct
     */
    public function testShowAllItems(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(Request::METHOD_GET, 'http://webserver/api/tablets/v1');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
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
     * @covers \App\Controller\V1\TabletApiController::show
     * @covers \App\Entity\Tablet::__construct
     */
    public function testShowSingleItem(): void
    {
        $client = $this->createAuthenticatedClient();
        $itemId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';
        $client->jsonRequest(Request::METHOD_GET, "http://webserver/api/tablets/v1/$itemId");

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
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
     * @covers \App\Controller\V1\TabletApiController::show
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testShowSingleItemFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $itemId = 'abcde';
        $client->jsonRequest(Request::METHOD_GET, "http://webserver/api/tablets/v1/$itemId");

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @covers \App\Controller\V1\TabletApiController::show
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testShowSingleItemFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $itemId = '66a5c0d8-4289-43ba-941a-e235f722c438';
        $client->jsonRequest(Request::METHOD_GET, "http://webserver/api/tablets/v1/$itemId");

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
