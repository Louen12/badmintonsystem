<?php

namespace App\Controller;

use App\Entity\Jour;
use App\Repository\JourRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JourController extends AbstractController
{

    // Route API pour récupérer la liste des jours
    #[Route('/jours', name: 'api_jour_list', methods: ['GET'])]
    public function list(JourRepository $jourRepository): JsonResponse
    {
        $jours = $jourRepository->findAll();

        // Transformer les entités en un tableau simple
        $data = array_map(function (Jour $jour) {
            return [
                'id' => $jour->getId(),
                'jour' => $jour->getJour(),
            ];
        }, $jours);

        return $this->json($data);
    }
}
