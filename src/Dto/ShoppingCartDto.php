<?php

namespace App\Dto;

use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Uuid;

readonly class ShoppingCartDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid(versions: Uuid::V4_RANDOM)]
        public UuidV4 $id,
        #[Assert\NotBlank]
        #[Assert\Type(\DateTimeInterface::class)]
        #[Assert\GreaterThan('now')]
        public \DateTime $expiresAt
    ) {
    }

    /**
     * @param array<string> $values
     */
    public static function fromScalarArray(array $values): self
    {
        return new self(
            UuidV4::fromString($values['id']),
            new \DateTime($values['expiresAt']),
        );
    }
}
