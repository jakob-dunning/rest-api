<?php

namespace App\Dto;

use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Uuid;

readonly class ProductDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid(versions: Uuid::V4_RANDOM)]
        public UuidV4 $id,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['Tablet', 'Laptop'])]
        public string $type,
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
}
