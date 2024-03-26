<?php

namespace Tests\Api\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Api\AuthenticatedClientTrait;

/**
 * @covers \App\EventSubscriber\JsonResponseEventSubscriber
 * @covers \App\Repository\ProductRepository
 */
class GetTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    /**
     * @covers \App\Controller\V1\ProductApiController::list
     * @covers \App\Entity\Product::__construct
     */
    public function testShowAllProducts(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->jsonRequest(Request::METHOD_GET, 'http://webserver/api/products/v1');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'data' => [
                    [
                        'id' => '44682a67-fa83-4216-9e9d-5ea5dd5bf480',
                        'type' => 'Tablet',
                        'manufacturer' => 'Lenovo',
                        'model' => 'Tab M9',
                        'price' => 19900
                    ],
                    [
                        'id' => '5c82f07f-3a47-422b-b423-efc3b782ec56',
                        'type' => 'Tablet',
                        'manufacturer' => 'Asus',
                        'model' => 'MeMO Pad HD 7',
                        'price' => 3110
                    ],
                    [
                        'id' => '0bdea651-825f-4648-9cac-4b03f8f4576e',
                        'type' => 'Tablet',
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
     * @covers \App\Controller\V1\ProductApiController::show
     * @covers \App\Entity\Product::__construct
     */
    public function testShowSingleProduct(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '44682a67-fa83-4216-9e9d-5ea5dd5bf480';
        $client->jsonRequest(Request::METHOD_GET, "http://webserver/api/products/v1/$productId");

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'data' => [
                    'id' => $productId,
                    'type' => 'Tablet',
                    'manufacturer' => 'Lenovo',
                    'model' => 'Tab M9',
                    'price' => 19900
                ]
            ],
            json_decode($client->getResponse()->getContent(), true)
        );
    }

    /**
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testShowSingleProductFailsWithInvalidId(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = 'abcde';
        $client->jsonRequest(Request::METHOD_GET, "http://webserver/api/products/v1/$productId");

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }

    /**
     * @Covers \App\EventSubscriber\HttpNotFoundEventSubscriber
     */
    public function testShowSingleProductFailsWithUnknownId(): void
    {
        $client = $this->createAuthenticatedClient();
        $productId = '66a5c0d8-4289-43ba-941a-e235f722c438';
        $client->jsonRequest(Request::METHOD_GET, "http://webserver/api/products/v1/$productId");

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue(key_exists('errors', $responseContentAsArray));
        $this->assertTrue(count($responseContentAsArray['errors']) > 0);
        $this->assertFalse(key_exists('data', $responseContentAsArray));
    }
}
