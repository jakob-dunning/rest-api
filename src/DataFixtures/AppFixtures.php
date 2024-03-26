<?php

namespace App\DataFixtures;

use App\Entity\ShoppingCart;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $lenovoTablet = new Product(
            UuidV4::fromString('44682a67-fa83-4216-9e9d-5ea5dd5bf480'),
            'Tablet',
            'Lenovo',
            'Tab M9',
            19900
        );

        $asusTablet = new Product(
            UuidV4::fromString('5c82f07f-3a47-422b-b423-efc3b782ec56'),
            'Tablet',
            'Asus',
            'MeMO Pad HD 7',
            3110
        );

        $samsungTablet = new Product(
            UuidV4::fromString('0bdea651-825f-4648-9cac-4b03f8f4576e'),
            'Tablet',
            'Samsung',
            'Galaxy Tab A9+',
            24799,
        );

        $manager->persist($lenovoTablet);
        $manager->persist($asusTablet);
        $manager->persist($samsungTablet);

        $shoppingCart = new ShoppingCart(
            UuidV4::fromString('5a2dc28e-1282-4e52-b90c-782c908a4e04'),
            new \DateTime('2024-03-17T12:44:00+00:00'),
        );
        $shoppingCart->addProduct($lenovoTablet);
        $shoppingCart->addProduct($asusTablet);
        $manager->persist($shoppingCart);

        $user = new User(
            Uuid::v4(),
            'test@test.com',
            '$2y$13$lvbKDQgxr//hyrRxPOedvupMn7kFo.SOe9qSZiaKHOLQGiSeqtsdG',
        );
        $manager->persist($user);

        $manager->flush();
    }
}
