<?php

namespace App\Entity;

use App\Dto\ShoppingCartDto;
use App\Repository\ShoppingCartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: ShoppingCartRepository::class)]
class ShoppingCart implements \JsonSerializable
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME)]
        private UuidV4 $id,
        #[ORM\Column(type: 'datetimetz')]
        private \DateTime $expiresAt,
        /** @var ArrayCollection<int, Product> */
        #[ORM\JoinTable]
        #[ORM\JoinColumn(onDelete: 'cascade')]
        #[ORM\InverseJoinColumn(unique: true, onDelete: 'cascade')]
        #[ORM\ManyToMany(targetEntity: Product::class)]
        private Collection $products = new ArrayCollection(),
    ) {
    }

    public static function fromDto(ShoppingCartDto $shoppingCartDto): self
    {
        return new self(
            $shoppingCartDto->id,
            $shoppingCartDto->expiresAt,
            new ArrayCollection()
        );
    }

    public function getId(): UuidV4
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function getExpiresAt(): \DateTime
    {
        return $this->expiresAt;
    }

    public function addProduct(Product $product): void
    {
        $this->products->add($product);
    }

    public function removeProduct(Product $product): void
    {
        $this->products->removeElement($product);
    }

    /**
     * @return array<string|array<string|int>>
     */
    public function toScalarArray(): array
    {
        return [
            'id' => $this->id->toRfc4122(),
            'expiresAt' => $this->expiresAt->format(DATE_ATOM),
            'products' => $this->products->toArray(),
        ];
    }

    /**
     * @return array<string|array<string|int>>
     */
    public function jsonSerialize(): array
    {
        return $this->toScalarArray();
    }

    public function mergeWithDto(ShoppingCartDto $shoppingCartDto): void
    {
        $this->id = $shoppingCartDto->id;
        $this->expiresAt = $shoppingCartDto->expiresAt;
    }
}
