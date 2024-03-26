<?php

namespace App\Controller\V1;

use App\Dto\ProductDto;
use App\Dto\ProductPatchDtoList;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\ValueResolver\ProductPatchDtoListArgumentResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products/v1', format: 'json')]
class ProductApiController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'], format: 'json')]
    public function list(): JsonResponse
    {
        $products = $this->productRepository->findAll();

        return new JsonResponse(
            ['data' => $products],
            Response::HTTP_OK,
        );
    }

    #[Route('/{id}', methods: ['GET'], format: 'json')]
    public function show(Product $product): JsonResponse
    {
        return new JsonResponse(
            ['data' => $product],
            Response::HTTP_OK,
        );
    }

    #[Route('', methods: ['POST'], format: 'json')]
    public function create(
        #[MapRequestPayload] ProductDto $productDto
    ): JsonResponse {
        $product = Product::fromDto($productDto);
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => $product],
            Response::HTTP_CREATED,
            ['Location' => sprintf('http://localhost/api/products/v1/%s', $product->getId())]
        );
    }

    #[Route('/{id}', methods: ['PATCH'], format: 'json')]
    public function update(
        Product $product,
        #[MapRequestPayload(acceptFormat: 'json', resolver: ProductPatchDtoListArgumentResolver::class)]
        ProductPatchDtoList $productPatchDtoList
    ): JsonResponse {
        $productAsArray = $product->toArray();

        foreach ($productPatchDtoList->patches as $patch) {
            $productAsArray[ltrim($patch->path, '/')] = $patch->value;
        }

        $productDto = new ProductDto(
            $productAsArray['id'],
            $productAsArray['type'],
            $productAsArray['manufacturer'],
            $productAsArray['model'],
            $productAsArray['price'],
        );
        $constraintViolationList = $this->validator->validate($productDto);

        if ($constraintViolationList->count() !== 0) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                '',
                new ValidationFailedException('', $constraintViolationList)
            );
        }

        $product->mergeWithDto($productDto);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => $product],
            Response::HTTP_OK,
            ['Location' => sprintf('http://localhost/api/products/v1/%s', $product->getId())]
        );
    }

    #[Route('/{id}', methods: ['DELETE'], format: 'json')]
    public function delete(Product $product): JsonResponse
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT,
        );
    }
}
