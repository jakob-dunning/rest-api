<?php

namespace App\Controller;

use App\Repository\TabletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\UuidV4;

#[Route('/api')]
class TabletApiController extends AbstractController
{
    private TabletRepository $tabletRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(TabletRepository $tabletRepository, EntityManagerInterface $entityManager)
    {
        $this->tabletRepository = $tabletRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/tablets', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $tablets = $this->tabletRepository->findAll();

        return new JsonResponse($tablets);
    }

    #[Route('/tablets/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        if(UuidV4::isValid($id) === false) {
            return new JsonResponse(['error' => 'Invalid uuid'], 400);
        }

        $tablet = $this->tabletRepository->find($id);

        return new JsonResponse($tablet);
    }
}