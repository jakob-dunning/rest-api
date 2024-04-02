<?php

namespace App\Entity;

use App\Dto\ProductDto;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product implements \JsonSerializable
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME)]
        private readonly UuidV4 $id,
        #[ORM\Column(length: 255)]
        private string $type,
        #[ORM\Column(length: 255)]
        private string $manufacturer,
        #[ORM\Column(length: 255)]
        private string $model,
        #[ORM\Column]
        private int $price
    ) {
    }

    public static function fromDto(ProductDto $productDto): self
    {
        return new self(
            $productDto->id,
            $productDto->type,
            $productDto->manufacturer,
            $productDto->model,
            $productDto->price
        );
    }

    public function getId(): UuidV4
    {
        return $this->id;
    }

    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<UuidV4|string|int>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'price' => $this->price,
        ];
    }

    /**
     * @return array<string|int>
     */
    public function toScalarArray(): array
    {
        return [
            'id' => $this->id->toRfc4122(),
            'type' => $this->type,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'price' => $this->price,
        ];
    }

    /**
     * @return array<string|int>
     */
    public function jsonSerialize(): array
    {
        return $this->toScalarArray();
    }

    public function mergeWithDto(ProductDto $productDto): void
    {
        $this->type = $productDto->type;
        $this->manufacturer = $productDto->manufacturer;
        $this->model = $productDto->model;
        $this->price = $productDto->price;
    }
}
