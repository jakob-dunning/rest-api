<?php

namespace App\Controller;

use App\Dto\TabletDto;
use App\Entity\Tablet;
use App\Repository\TabletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tablets')]
class TabletApiController extends AbstractController
{
    public function __construct(
        private TabletRepository $tabletRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $tablets = $this->tabletRepository->findAll();

        return new JsonResponse(
            ['data' => $tablets],
            Response::HTTP_OK,
        );
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Tablet $tablet): JsonResponse
    {
        return new JsonResponse(
            ['data' => $tablet],
            Response::HTTP_OK,
        );
    }

    #[Route('', methods: ['POST'], format: 'json')]
    public function create(#[MapRequestPayload] TabletDto $tabletDto): JsonResponse
    {
        $tablet = Tablet::fromDto($tabletDto);
        $this->entityManager->persist($tablet);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => sprintf('http://localhost/api/tablets/%s', $tablet->getId()->toRfc4122())],
            201,
        );
    }

    #[Route('/{id}', methods: ['PATCH'], format: 'json')]
    public function update(Tablet $tablet, Request $request): JsonResponse
    {
        if (in_array('id', $request->getPayload()->keys())) {
            return new JsonResponse(
                [
                    'errors' => [
                        'status' => Response::HTTP_BAD_REQUEST,
                        'title' => 'Property "id" can not be changed'
                    ]
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $tabletAsScalarArray = $tablet->toScalarArray();

        foreach ($request->getPayload()->keys() as $key) {
            if (key_exists($key, $tabletAsScalarArray) === false) {
                return new JsonResponse(
                    ['errors' => ["No field found with name: $key"]],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $tabletAsScalarArray[$key] = $request->getPayload()->get($key);
        }

        $tabletDto = TabletDto::fromScalarArray($tabletAsScalarArray);
        $constraintViolationList = $this->validator->validate($tabletDto);

        if ($constraintViolationList->count() !== 0) {
            $errorMessages = [];
            foreach ($constraintViolationList as $violation) {
                $errorMessages[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return new JsonResponse(
                ['errors' => $errorMessages],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $tablet->mergeWithDto($tabletDto);

        $this->entityManager->persist($tablet);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => $tablet],
            Response::HTTP_OK,
        );
    }

    #[Route('/{id}', methods: ['DELETE'], format: 'json')]
    public function delete(Tablet $tablet): JsonResponse
    {
        $this->entityManager->remove($tablet);
        $this->entityManager->flush();

        return new JsonResponse(
            ['data' => ''],
            Response::HTTP_NO_CONTENT,
        );
    }
}
