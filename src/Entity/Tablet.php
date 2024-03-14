<?php

namespace App\Entity;

use App\Repository\TabletRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: TabletRepository::class)]
class Tablet implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private ?UuidV4 $id = null;

    #[ORM\Column(length: 255)]
    private ?string $manufacturer = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column]
    private ?int $price = null;

    public function getId(): ?UuidV4
    {
        return $this->id;
    }

    public function setId(UuidV4 $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'price' => $this->price,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
