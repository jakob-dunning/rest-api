<?php

namespace App\Entity;

use App\Dto\TabletDto;
use App\Repository\TabletRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: TabletRepository::class)]
class Tablet implements \JsonSerializable
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME)]
        private UuidV4 $id,
        #[ORM\Column(length: 255)]
        private string $manufacturer,
        #[ORM\Column(length: 255)]
        private string $model,
        #[ORM\Column]
        private int $price
    ) {
    }

    public static function fromDto(TabletDto $tabletDto): self
    {
        return new self(
            UuidV4::fromString($tabletDto->id),
            $tabletDto->manufacturer,
            $tabletDto->model,
            $tabletDto->price
        );
    }

    /**
     * @param array<UuidV4|string|int> $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            UuidV4::fromString($values['id']),
            $values['manufacturer'],
            $values['model'],
            $values['price']
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

    public function setManufacturer(string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getModel(): string
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

    /**
     * @return array<string|int>
     */
    public function toScalarArray(): array
    {
        return [
            'id' => $this->id->toRfc4122(),
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

    public function mergeWithDto(TabletDto $tabletDto): void
    {
        $this->id = $tabletDto->id;
        $this->manufacturer = $tabletDto->manufacturer;
        $this->model = $tabletDto->model;
        $this->price = $tabletDto->price;
    }
}
