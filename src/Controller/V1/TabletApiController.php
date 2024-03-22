<?php

namespace App\Controller\V1;

use App\Dto\TabletDto;
use App\Dto\TabletPatchDtoList;
use App\Entity\Tablet;
use App\Repository\TabletRepository;
use App\ValueResolver\TabletPatchDtoListArgumentResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tablets/v1')]
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
            ['data' => sprintf('http://localhost/api/tablets/v1/%s', $tablet->getId()->toRfc4122())],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', methods: ['PATCH'], format: 'json')]
    public function update(
        Tablet $tablet,
        #[MapRequestPayload(acceptFormat: 'json', resolver: TabletPatchDtoListArgumentResolver::class)]
        TabletPatchDtoList $tabletPatchDtoList
    ): JsonResponse {
        $tabletAsScalarArray = $tablet->toScalarArray();

        foreach ($tabletPatchDtoList->patches as $patch) {
            $tabletAsScalarArray[ltrim($patch->path, '/')] = $patch->value;
        }

        $tabletDto = TabletDto::fromScalarArray($tabletAsScalarArray);
        $constraintViolationList = $this->validator->validate($tabletDto);

        if ($constraintViolationList->count() !== 0) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                '',
                new ValidationFailedException('', $constraintViolationList)
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
