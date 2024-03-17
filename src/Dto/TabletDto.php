<?php

namespace App\Dto;

use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Uuid;

readonly class TabletDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid(versions: Uuid::V4_RANDOM)]
        public UuidV4 $id,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Length(min: 1, max: 255)]
        public string $manufacturer,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Length(min: 1, max: 255)]
        public string $model,
        #[Assert\Type('int')]
        #[Assert\Range(min: 0, max: 9999999)]
        public int $price
    ) {
    }

    /**
     * @param array<UuidV4|string|int> $values
     */
    public static function fromScalarArray(array $values): self
    {
        return new self(
            UuidV4::fromString($values['id']),
            $values['manufacturer'],
            $values['model'],
            $values['price'],
        );
    }
}
