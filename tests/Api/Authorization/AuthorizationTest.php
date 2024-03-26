<?php

namespace Api\Authorization;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \App\Repository\UserRepository
 * @covers \App\Entity\User
 */
class AuthorizationTest extends WebTestCase
{
    public function testUserCanCreateToken(): void
    {
        $client = $this->createClient();
        $username = 'test@test.com';
        $client->jsonRequest(
            Request::METHOD_POST,
            'http://webserver/api/login_check',
            [
                'username' => $username,
                'password' => 'test'
            ]
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $JWTEncoder = $this->getContainer()->get(JWTEncoderInterface::class);
        $responseContentAsArray = json_decode($client->getResponse()->getContent(), true);
        $decodedToken = $JWTEncoder->decode($responseContentAsArray['token']);
        $this->assertEquals($username, $decodedToken['username']);
    }
}
