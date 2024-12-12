<?php

namespace App\GraphQL;

use App\Repository\CreneauHoraireRepository;
use App\Repository\ReservationRepository;
use App\Repository\TerrainRepository;

class AvailableSlotsResolver
{
    private CreneauHoraireRepository $creneauHoraireRepository;
    private ReservationRepository $reservationRepository;
    private TerrainRepository $terrainRepository;

    public function __construct(
        CreneauHoraireRepository $creneauHoraireRepository,
        ReservationRepository $reservationRepository,
        TerrainRepository $terrainRepository
    ) {
        $this->creneauHoraireRepository = $creneauHoraireRepository;
        $this->reservationRepository = $reservationRepository;
        $this->terrainRepository = $terrainRepository;
    }

    public function __invoke(array $args): array
    {
        $date = $args['date'];
        $terrainName = $args['terrain'];

        // Récupérer le terrain
        $terrain = $this->terrainRepository->findOneBy(['nom' => $terrainName]);
        if (!$terrain) {
            throw new \InvalidArgumentException("Terrain non trouvé : $terrainName");
        }

        // Récupérer les créneaux horaires
        $creneaux = $this->creneauHoraireRepository->findAll();

        // Récupérer les réservations pour ce terrain et ce jour
        $reservations = $this->reservationRepository->findBy([
            'terrain' => $terrain,
            'jour' => $date, // Attention : modifie selon le format de date
        ]);

        // Identifier les créneaux réservés
        $reservedSlots = array_map(fn($reservation) => $reservation->getCreneau()->getId(), $reservations);

        // Préparer le résultat
        $result = [];
        foreach ($creneaux as $creneau) {
            $result[] = [
                'time' => $creneau->getCreneaux(),
                'isAvailable' => !in_array($creneau->getId(), $reservedSlots),
            ];
        }

        return $result;
    }
}