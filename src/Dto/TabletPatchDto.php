<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TabletPatchDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['replace'])]
        public string $operation,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Choice(choices: ['/manufacturer', '/model', '/price'])]
        public string $path,
        #[Assert\Type('string')]
        public ?string $value,
        #[Assert\Type('string')]
        public ?string $from
    ) {
    }
}
