<?php

namespace App\Controller;

use App\Entity\Jour;
use App\Entity\Reservation;
use App\Repository\CreneauHoraireRepository;
use App\Repository\JourRepository;
use App\Repository\ReservationRepository;
use App\Repository\TerrainRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservations/{id}', name: 'api_reservation_by_id', methods: ['GET'])]
    public function getReservationById(
        int $id,
        ReservationRepository $reservationRepository
    ): JsonResponse {
        // Récupérer la réservation par ID
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['message' => 'Réservation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Préparer les données pour la réponse
        $reservationData = [
            'id' => $reservation->getId(),
            'utilisateur' => $reservation->getUtilisateur()->getPseudo(),
            'terrain' => $reservation->getTerrain()->getNom(),
            'jour' => $reservation->getJour()->getJour(),
            'creneau' => $reservation->getCreneau()->getCreneau(),
        ];

        return new JsonResponse($reservationData, Response::HTTP_OK);
    }

    #[Route('/reservations', name: 'api_reservation_create', methods: ['POST'])]
    public function createReservation(
        Request $request,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository,
        TerrainRepository $terrainRepository,
        JourRepository $jourRepository,
        CreneauHoraireRepository $creneauRepository
    ): JsonResponse {
        // Décoder les données JSON envoyées
        $data = json_decode($request->getContent(), true);

        $pseudo = $data['pseudo'] ?? null;
        $terrainId = $data['terrain'] ?? null;
        $jourId = $data['jour'] ?? null;
        $creneauId = $data['creneau'] ?? null;

        if (!$pseudo || !$terrainId || !$jourId || !$creneauId) {
            return new JsonResponse(['message' => 'Données manquantes'], 400);
        }

        // Vérifier si l'utilisateur existe et qu'il n'est pas admin
        $utilisateur = $utilisateurRepository->findOneBy(['pseudo' => $pseudo]);

        if (!$utilisateur) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        if ($utilisateur->isAdmin()) {
            return new JsonResponse(['message' => 'Les administrateurs ne peuvent pas réserver'], 403);
        }

        // Récupérer les entités Terrain, Jour, et Creneau
        $terrain = $terrainRepository->findOneBy(['nom' => $terrainId]);
        $jour = $jourRepository->findOneBy(['jour' => $jourId]);
        $creneau = $creneauRepository->findOneBy(['creneau' =>$creneauId]);

        if (!$terrain || !$jour || !$creneau) {
            return new JsonResponse(['message' => 'Terrain, jour ou créneau introuvable'], 404);
        }

        // Vérifier si le créneau est déjà réservé
        $existingReservation = $entityManager->getRepository(Reservation::class)
            ->findOneBy([
                'terrain' => $terrain,
                'jour' => $jour,
                'creneau' => $creneau
            ]);

        if ($existingReservation) {
            return new JsonResponse(['message' => 'Ce créneau est déjà réservé'], 409);
        }

        // Créer la réservation
        $reservation = new Reservation();
        $reservation->setUtilisateur($utilisateur);
        $reservation->setTerrain($terrain);
        $reservation->setJour($jour);
        $reservation->setCreneau($creneau);

        $entityManager->persist($reservation);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Réservation effectuée avec succès'], 201);
    }

    #[Route('/reservations', name: 'api_reservations_list', methods: ['GET'])]
    public function listReservations(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $pseudo = $request->query->get('pseudo'); // Filtrer par pseudo
        $terrain = $request->query->get('terrain'); // Filtrer par terrain

        // Construire une requête dynamique en fonction des paramètres
        $criteria = [];
        if ($pseudo) {
            $criteria['utilisateur.pseudo'] = $pseudo;
        }
        if ($terrain) {
            $criteria['terrain.nom'] = $terrain;
        }

        $reservations = $entityManager->getRepository(Reservation::class)->findBy($criteria);

        // Préparer les données
        $reservationsData = [];
        foreach ($reservations as $reservation) {
            $reservationsData[] = [
                'id' => $reservation->getId(),
                'utilisateur' => $reservation->getUtilisateur()->getPseudo(),
                'terrain' => $reservation->getTerrain()->getNom(),
                'jour' => $reservation->getJour()->getJour(),
                'creneau' => $reservation->getCreneau()->getCreneau(),
            ];
        }

        return new JsonResponse($reservationsData, 200);
    }
}
