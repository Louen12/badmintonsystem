<?php

namespace App\Controller;

use App\Repository\TerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TerrainController extends AbstractController
{
    #[Route('/terrains', name: 'api_terrain_list', methods: ['GET'])]
    public function index(TerrainRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $terrains = $repository->findAll();

        // Prépare les données manuellement pour éviter les problèmes de sérialisation
        $terrainsData = [];
        foreach ($terrains as $terrain) {
            $terrainsData[] = [
                'id' => $terrain->getId(),
                'nom' => $terrain->getNom(),
                'actif' => $terrain->isActif(),
            ];
        }

        // Retourne la réponse en JSON
        return new JsonResponse($terrainsData, JsonResponse::HTTP_OK);
    }

    #[Route('/terrains/disponible', name: 'api_terrain_list_disponible', methods: ['GET'])]
    public function terrainsActifs(TerrainRepository $repository): JsonResponse
    {
        // Récupère tous les terrains actifs
        $terrains = $repository->findBy(['actif' => 1]);

        // Prépare les données manuellement pour éviter les problèmes de sérialisation
        $terrainsData = [];
        foreach ($terrains as $terrain) {
            $terrainsData[] = [
                'id' => $terrain->getId(),
                'nom' => $terrain->getNom(),
                'actif' => $terrain->isActif(),
            ];
        }

        // Retourne la réponse en JSON
        return new JsonResponse($terrainsData, JsonResponse::HTTP_OK);
    }

    #[Route('/terrain/disponible/{id}', name: 'app_terrain_disponible', methods: ['PATCH'])]
    public function rendreDisponible(
        int $id,
        TerrainRepository $repository,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse {
        $terrain = $repository->find($id);

        if (!$terrain) {
            return new JsonResponse(['message' => 'Terrain non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($terrain->isActif() === true) {
            return new JsonResponse(['message' => 'Le terrain est déjà disponible'], Response::HTTP_BAD_REQUEST);
        }

        $terrain->setActif(true);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Terrain rendu disponible'], Response::HTTP_OK);
    }


    #[Route('/terrain/indisponible/{id}', name: 'api_terrain_indisponible', methods: ['PATCH'])]
    public function rendreIndisponible(
        int $id,
        TerrainRepository $repository,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse {


        $terrain = $repository->find($id);

        if (!$terrain) {
            return new JsonResponse(['message' => 'Terrain non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($terrain->isActif() === false) {
            return new JsonResponse(['message' => 'Le terrain est déjà indisponible'], Response::HTTP_BAD_REQUEST);
        }

        $terrain->setActif(false);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Terrain rendu indisponible'], Response::HTTP_OK);
    }
}
