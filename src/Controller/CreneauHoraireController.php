<?php

namespace App\Controller;

use App\Repository\CreneauHoraireRepository;
use App\Repository\JourRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreneauHoraireController extends AbstractController
{
    #[Route('/creneaux/horaire', name: 'api_creneau_horaire_list')]
    public function listCreneauxHoraires(CreneauHoraireRepository $creneauHoraireRepository): JsonResponse
    {
        // Récupérer tous les créneaux horaires
        $creneaux = $creneauHoraireRepository->findAll();

        // Préparer les données pour la réponse
        $creneauxData = [];
        foreach ($creneaux as $creneau) {
            $creneauxData[] = [
                'id' => $creneau->getId(),
                'creneaux' => $creneau->getCreneau(), // Assure-toi que cette méthode existe dans l'entité CreneauHoraire
            ];
        }

        // Retourner la réponse JSON
        return new JsonResponse($creneauxData, 200);
    }

    #[Route('/creneaux/{jour}', name: 'api_creneaux_par_jour', methods: ['GET'])]
    public function afficherCreneauxParJour(
        $jour, // Renommé pour correspondre à {jour} dans la route
        CreneauHoraireRepository $creneauRepository,
        ReservationRepository $reservationRepository,
        JourRepository $jourRepository
    ): JsonResponse {
        // Vérifie si le jour existe
        $jourEntity = $jourRepository->findOneBy(['jour' => $jour]);
        if (!$jourEntity) {
            return new JsonResponse(['message' => 'Jour non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Récupère tous les créneaux horaires
        $creneaux = $creneauRepository->findAll();

        // Récupère les réservations pour le jour donné
        $reservations = $reservationRepository->findBy(['jour' => $jourEntity]);

        // Marque les créneaux réservés
        $reservedSlots = [];
        foreach ($reservations as $reservation) {
            $reservedSlots[] = $reservation->getCreneau()->getId();
        }

        // Structure la réponse : liste des créneaux et leur statut
        $result = [];
        foreach ($creneaux as $creneau) {
            $result[] = [
                'creneau' => $creneau->getCreneau(), // Assure-toi que getCreneaux() existe
                'isReserved' => in_array($creneau->getId(), $reservedSlots),
            ];
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }
}
