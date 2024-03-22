<?php

namespace App\Controller\V1;

use App\Dto\ShoppingCartPatchDtoList;
use App\Dto\ShoppingCartDto;
use App\Entity\ShoppingCart;
use App\Entity\Tablet;
use App\ValueResolver\ShoppingCartPatchDtoListArgumentResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/shopping-carts/v1', format: 'json')]
class ShoppingCartApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/{shoppingCart}', methods: ['GET'], format: 'json')]
    public function show(ShoppingCart $shoppingCart): JsonResponse
    {
        return new JsonResponse(
            ['data' => $shoppingCart],
            Response::HTTP_OK,
        );
    }

    #[Route('', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] ShoppingCartDto $shoppingCartDto): JsonResponse
    {
        $shoppingCart = ShoppingCart::fromDto($shoppingCartDto);
        $this->entityManager->persist($shoppingCart);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => $shoppingCart],
            Response::HTTP_CREATED,
            ['Location' => sprintf('http://localhost/api/shopping-carts/v1/%s', $shoppingCart->getId())]
        );
    }

    #[Route('/{shoppingCart}/tablets', methods: ['POST'], format: 'json')]
    public function addTablet(
        ShoppingCart $shoppingCart,
        #[MapEntity(expr: 'repository.find(request.getPayload().get("id") ?? "")')]
        Tablet $tablet
    ): JsonResponse {
        $shoppingCart->addTablet($tablet);
        $this->entityManager->persist($shoppingCart);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => $shoppingCart],
            Response::HTTP_CREATED,
            [
                'Location' => sprintf(
                    'http://localhost/api/shopping-carts/v1/%s/tablets/%s',
                    $shoppingCart->getId(),
                    $tablet->getId()
                )
            ]
        );
    }

    #[Route('/{shoppingCart}', methods: ['PATCH'], format: 'json')]
    public function update(
        ShoppingCart $shoppingCart,
        #[MapRequestPayload(acceptFormat: 'json', resolver: ShoppingCartPatchDtoListArgumentResolver::class)]
        ShoppingCartPatchDtoList $shoppingCartPatchDtoList
    ): JsonResponse {
        $shoppingCartAsScalarArray = $shoppingCart->toScalarArray();

        foreach ($shoppingCartPatchDtoList->patches as $patch) {
            $shoppingCartAsScalarArray[ltrim($patch->path, '/')] = $patch->value;
        }

        $shoppingCartDto = ShoppingCartDto::fromScalarArray($shoppingCartAsScalarArray);
        $constraintViolationList = $this->validator->validate($shoppingCartDto);

        if ($constraintViolationList->count() !== 0) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                '',
                new ValidationFailedException('', $constraintViolationList)
            );
        }

        $shoppingCart->mergeWithDto($shoppingCartDto);

        $this->entityManager->persist($shoppingCart);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => $shoppingCart],
            Response::HTTP_OK,
            ['Location' => sprintf('http://localhost/api/shopping-carts/v1/%s', $shoppingCart->getId())]
        );
    }

    #[Route('/{shoppingCart}', methods: ['DELETE'], format: 'json')]
    public function delete(ShoppingCart $shoppingCart): JsonResponse
    {
        $this->entityManager->remove($shoppingCart);
        $this->entityManager->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT,
        );
    }

    #[Route('/{shoppingCart}/tablets/{tablet}', methods: ['DELETE'], format: 'json')]
    public function removeTablet(ShoppingCart $shoppingCart, Tablet $tablet): JsonResponse
    {
        $shoppingCart->removeTablet($tablet);
        $this->entityManager->persist($shoppingCart);
        $this->entityManager->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT,
        );
    }
}
