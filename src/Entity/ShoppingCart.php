<?php

namespace App\Entity;

use App\Repository\ShoppingCartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: ShoppingCartRepository::class)]
class ShoppingCart implements \JsonSerializable
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: UuidType::NAME)]
        private UuidV4          $id,
        #[ORM\Column(type: 'datetimetz')]
        private \DateTime       $expiresAt,
        /**
         * @var ArrayCollection<int, Tablet>
         */
        #[ORM\JoinTable(name: 'tablets_in_shoppingcarts')]
        #[ORM\JoinColumn(name: 'shoppingcart_id', referencedColumnName: 'id')]
        #[ORM\InverseJoinColumn(name: 'tablet_id', referencedColumnName: 'id', unique: true)]
        #[ORM\ManyToMany(targetEntity: Tablet::class)]
        private ArrayCollection $tablets,
    )
    {
    }

    public function getId(): UuidV4
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection<int, Tablet>
     */
    public function getTablets(): ArrayCollection
    {
        return $this->tablets;
    }

    public function getExpiresAt(): \DateTime
    {
        return $this->expiresAt;
    }

    /**
     * @return array<string|int|array<string|int>>
     */
    public function toScalarArray(): array
    {
        return [
            'id' => $this->id->toRfc4122(),
            'expiresAt' => $this->expiresAt->format(DATE_ATOM),
            'tablets' => $this->tablets,
        ];
    }

    /**
     * @return array<string|int|array<string|int>>
     */
    public function jsonSerialize(): array
    {
        return $this->toScalarArray();
    }
}
