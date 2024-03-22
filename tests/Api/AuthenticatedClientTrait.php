<?php

namespace Tests\Api;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticatedClientTrait
{
    private function createAuthenticatedClient(): KernelBrowser
    {
        $client = $this->createClient();
        /* @var LcobucciJWTEncoder $encoder */
        $encoder = $client->getContainer()->get(JWTEncoderInterface::class);
        $client->setServerParameter(
            'HTTP_Authorization',
            sprintf('Bearer %s', $encoder->encode(['username' => 'test@test.com']))
        );

        return $client;
    }
}
