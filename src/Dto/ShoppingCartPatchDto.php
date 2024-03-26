<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ShoppingCartPatchDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['replace'])]
        public string $operation,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['/expiresAt'])]
        public string $path,
        #[Assert\Type('string')]
        public string|null $value,
        #[Assert\Type('string')]
        public string|null $from
    ) {
    }
}