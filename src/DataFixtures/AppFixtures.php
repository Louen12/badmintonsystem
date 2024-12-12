<?php

namespace App\DataFixtures;

use App\Entity\CreneauHoraire;
use App\Entity\Jour;
use App\Entity\Terrain;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new Utilisateur();
        $user->setPseudo("Louen");
        $user->setIsAdmin(false);
        $user->setPassword($this->passwordHasher->hashPassword($user, "root"));
        $manager->persist($user);

        $user = new Utilisateur();
        $user->setPseudo("Paul");
        $user->setIsAdmin(false);
        $user->setPassword($this->passwordHasher->hashPassword($user, "root"));
        $manager->persist($user);

        // Ajout de l'utilisateur admin
        $admin = new Utilisateur();
        $admin->setPseudo('amybad');
        $admin->setIsAdmin(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'astrongpassword'));
        $manager->persist($admin);

        // Ajout des terrains
        $terrains = ['Terrain_A', 'Terrain_B', 'Terrain_C', 'Terrain_D'];
        foreach ($terrains as $terrainNom) {
            $terrain = new Terrain();
            $terrain->setNom($terrainNom);
            $terrain->setActif(true);
            $manager->persist($terrain);
        }

        // Ajout des jours
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        foreach ($jours as $jourNom) {
            $jour = new Jour();
            $jour->setJour($jourNom);
            $manager->persist($jour);
        }

        // Ajout des créneaux horaires
        $creneaux = [
            '10h-10h45', '10h45-11h30', '11h30-12h15', '12h15-13h', '13h-13h45',
            '13h45-14h30', '14h30-15h15', '15h15-16h', '16h-16h45', '16h45-17h30',
            '17h30-18h15', '18h15-19h', '19h-19h45', '20h30-21H15', '21h15-22H'
        ];
        foreach ($creneaux as $creneauNom) {
            $creneau = new CreneauHoraire();
            $creneau->setCreneau($creneauNom);
            $manager->persist($creneau);
        }

        $manager->flush();
    }
}
