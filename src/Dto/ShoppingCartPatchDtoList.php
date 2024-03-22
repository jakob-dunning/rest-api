<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ShoppingCartPatchDtoList
{
    public function __construct(
        /** @var array<ShoppingCartPatchDto> $patches */
        #[Assert\Valid]
        public array $patches = [],
    ) {
    }
}
