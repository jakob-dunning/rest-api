<?php

namespace App\DataFixtures;

use App\Entity\ShoppingCart;
use App\Entity\Tablet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\UuidV4;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $entityManager): void
    {
        $lenovoTabletFixture = [
            'id' => UuidV4::fromString('44682a67-fa83-4216-9e9d-5ea5dd5bf480'),
            'manufacturer' => 'Lenovo',
            'model' => 'Tab M9',
            'price' => 19900
        ];

        $asusTabletFixture = [
            'id' => UuidV4::fromString('5c82f07f-3a47-422b-b423-efc3b782ec56'),
            'manufacturer' => 'Asus',
            'model' => 'MeMO Pad HD 7',
            'price' => 3110
        ];

        $samsungTabletFixture = [
            'id' => UuidV4::fromString('0bdea651-825f-4648-9cac-4b03f8f4576e'),
            'manufacturer' => 'Samsung',
            'model' => 'Galaxy Tab A9+',
            'price' => 24799
        ];

        $lenovoTablet = Tablet::fromArray($lenovoTabletFixture);
        $entityManager->persist($lenovoTablet);
        $asusTablet = Tablet::fromArray($asusTabletFixture);
        $entityManager->persist($asusTablet);
        $samsungTablet = Tablet::fromArray($samsungTabletFixture);
        $entityManager->persist($samsungTablet);

        $shoppingCartFixture = [
            'id' => '5a2dc28e-1282-4e52-b90c-782c908a4e04',
            'expiresAt' => '2024-03-17T12:44:00+00:00',
        ];

        $shoppingCart = ShoppingCart::fromArray($shoppingCartFixture);
        $shoppingCart->addTablet($lenovoTablet);
        $shoppingCart->addTablet($samsungTablet);
        $entityManager->persist($shoppingCart);

        $entityManager->flush();
    }
}
