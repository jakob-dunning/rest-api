<?php

namespace App\Entity;

use App\Dto\ShoppingCartDto;
use App\Dto\TabletDto;
use App\Repository\ShoppingCartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: ShoppingCartRepository::class)]
class ShoppingCart implements \JsonSerializable
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME)]
        private UuidV4 $id,
        #[ORM\Column(type: 'datetimetz')]
        private \DateTime $expiresAt,
        /**
         * @var ArrayCollection<int, Tablet>
         */
        #[ORM\JoinTable(name: 'tablets_in_shoppingcarts')]
        #[ORM\JoinColumn(name: 'shoppingcart_id', referencedColumnName: 'id', onDelete: 'cascade')]
        #[ORM\InverseJoinColumn(name: 'tablet_id', referencedColumnName: 'id', unique: true, onDelete: 'cascade')]
        #[ORM\ManyToMany(targetEntity: Tablet::class)]
        private Collection $tablets = new ArrayCollection(),
    ) {
    }

    /**
     * @param array<string> $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            UuidV4::fromString($values['id']),
            new \DateTime($values['expiresAt']),
            new ArrayCollection()
        );
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
     * @return ArrayCollection<int, Tablet>
     */
    public function getTablets(): Collection
    {
        return $this->tablets;
    }

    public function getExpiresAt(): \DateTime
    {
        return $this->expiresAt;
    }

    public function addTablet(Tablet $tablet): void
    {
        //TODO: Handle adding same tablet twice
        $this->tablets->add($tablet);
    }

    public function removeTablet(Tablet $tablet): void
    {
        //TODO: Handle removing tablet not in collection
        $this->tablets->removeElement($tablet);
    }

    /**
     * @return array<string|array<string|int>>
     */
    public function toScalarArray(): array
    {
        return [
            'id' => $this->id->toRfc4122(),
            'expiresAt' => $this->expiresAt->format(DATE_ATOM),
            'tablets' => $this->tablets->toArray(),
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
