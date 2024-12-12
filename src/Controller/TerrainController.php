<?php

namespace App\Controller;

use App\Repository\TerrainRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TerrainController extends AbstractController
{
    #[Route('/terrain', name: 'app_terrain')]
    public function index(TerrainRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $terrains = $repository->findAll();

        $jsonTerrains = $serializer->serialize($terrains, 'json');
        return new JsonResponse($jsonTerrains, Response::HTTP_OK, [], true);
    }
}
