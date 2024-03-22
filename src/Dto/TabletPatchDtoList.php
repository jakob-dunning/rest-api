<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TabletPatchDtoList
{
    public function __construct(
        /** @var array<TabletPatchDto> $patches */
        #[Assert\Valid]
        public array $patches = [],
    ) {
    }
}
