<?php

namespace App\Dto;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Uuid;

class ProductDto
{
    private function __construct(
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

    public static function fromProduct(Product $product): self
    {
        return new self(
            $product->getId(),
            $product->getType(),
            $product->getManufacturer(),
            $product->getModel(),
            $product->getPrice()
        );
    }

    /**
     * @throws \TypeError
     * @throws \Exception
     */
    public function updateFromRequest(Request $request): void
    {
        $properties = ['type', 'manufacturer', 'model', 'price'];

        if (count(array_diff($request->getPayload()->keys(), $properties)) > 0) {
            throw new \Exception(sprintf('Unknown property in [%s]', implode(',', $properties)));
        }

        if ($request->getPayload()->get('type') !== null) {
            $this->type = $request->getPayload()->get('type');
        }

        if ($request->getPayload()->get('manufacturer') !== null) {
            $this->manufacturer = $request->getPayload()->get('manufacturer');
        }

        if ($request->getPayload()->get('model') !== null) {
            $this->model = $request->getPayload()->get('model');
        }

        if ($request->getPayload()->get('price') !== null) {
            $this->price = $request->getPayload()->get('price');
        }
    }
}
