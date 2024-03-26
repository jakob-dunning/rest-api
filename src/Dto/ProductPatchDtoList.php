<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProductPatchDtoList
{
    public function __construct(
        /** @var array<ProductPatchDto> $patches */
        #[Assert\Valid]
        public array $patches = [],
    ) {
    }
}
