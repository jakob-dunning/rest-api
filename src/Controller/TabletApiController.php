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
        $tabletAsScalarArray = $tablet->toScalarArray();

        foreach ($request->getPayload()->all() as $patch) {
            if (in_array(ltrim($patch['path'], '/'), ['manufacturer','model', 'price']) === false) {
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

            $tabletAsScalarArray[ltrim($patch['path'], '/')] = $patch['value'];
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
            null,
            Response::HTTP_NO_CONTENT,
        );
    }
}
