<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProductPatchDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['replace'])]
        public string $operation,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['/manufacturer', '/model', '/price', '/type'])]
        public string $path,
        #[Assert\Type(['string', 'int'])]
        public string|int|null $value,
        #[Assert\Type('string')]
        public string|null $from
    ) {
    }
}
