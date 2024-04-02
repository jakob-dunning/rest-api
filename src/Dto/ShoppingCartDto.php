<?php

namespace App\Dto;

use App\Entity\Product;
use App\Entity\ShoppingCart;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Uuid;

class ShoppingCartDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid(versions: Uuid::V4_RANDOM)]
        public UuidV4 $id,
        #[Assert\NotBlank]
        #[Assert\Type(\DateTimeInterface::class)]
        #[Assert\GreaterThan('now')]
        public \DateTime $expiresAt,
        /** @var ArrayCollection<int, Product> */
        #[Assert\Valid]
        public Collection $products = new ArrayCollection()
    ) {
    }

    public static function fromShoppingCart(ShoppingCart $shoppingCart): self
    {
        return new self(
            $shoppingCart->getId(),
            $shoppingCart->getExpiresAt(),
            $shoppingCart->getProducts()
        );
    }

    /**
     * @throws \TypeError
     * @throws \Exception
     */
    public function updateFromRequest(Request $request): void
    {
        $properties = ['expiresAt'];

        if (count(array_diff($request->getPayload()->keys(), $properties)) > 0) {
            throw new \Exception(sprintf('Unknown property in [%s]', implode(',', $properties)));
        }

        if ($request->getPayload()->get('expiresAt') !== null) {
            $this->expiresAt = new \DateTime($request->getPayload()->get('expiresAt'));
        }
    }
}
