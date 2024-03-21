<?php

namespace App\Controller;

use App\Dto\ShoppingCartDto;
use App\Entity\ShoppingCart;
use App\Entity\Tablet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/shopping-carts')]
class ShoppingCartApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('/{shoppingCart}', methods: ['GET'])]
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
            ['data' => sprintf('http://localhost/api/shopping-carts/%s', $shoppingCart->getId()->toRfc4122())],
            201,
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
            [
                'data' => sprintf(
                    'http://localhost/api/shopping-carts/%s/tablets/%s',
                    $shoppingCart->getId()->toRfc4122(),
                    $tablet->getId()->toRfc4122()
                )
            ],
            201,
        );
    }

    #[Route('/{shoppingCart}', methods: ['PATCH'], format: 'json')]
    public function update(ShoppingCart $shoppingCart, Request $request): JsonResponse
    {
        $shoppingCartAsScalarArray = $shoppingCart->toScalarArray();

        foreach ($request->getPayload()->all() as $patch) {
            if (in_array(ltrim($patch['path'], '/'), ['expiresAt']) === false) {
                return new JsonResponse(
                    [
                        'errors' => [
                            'status' => Response::HTTP_BAD_REQUEST,
                            'title' => "Patch path \"{$patch['path']}\" not found or read-only"
                        ]
                    ],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            if ($patch['op'] !== 'replace') {
                return new JsonResponse(
                    [
                        'errors' => [
                            'status' => Response::HTTP_BAD_REQUEST,
                            'title' => "Patch operation \"{$patch['op']}\" not possible on entity"
                        ]
                    ],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            if ($patch['value'] === '') {
                return new JsonResponse(
                    [
                        'errors' => [
                            'status' => Response::HTTP_BAD_REQUEST,
                            'title' => "Patch value cannot be empty"
                        ]
                    ],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $shoppingCartAsScalarArray[ltrim($patch['path'], '/')] = $patch['value'];
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
