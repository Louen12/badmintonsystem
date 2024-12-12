# Projet Symfony : Gestion des Ressources et Utilisateurs

## Table des Matières
- [Lancer le projet](#lancer-le-projet)
- [Utiliser le service](#utiliser-le-service)
- [Conception](#conception)
    - [Dictionnaire des données](#dictionnaire-des-données)
    - [Tableau récapitulatif des ressources](#tableau-récapitulatif-des-ressources)
- [Sécurité](#sécurité)
- [Remarques](#remarques)
- [Références](#références)

---

## Lancer le projet

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- Symfony CLI
- Serveur de base de données (MySQL, PostgreSQL, etc.)

### Étapes
1. Clonez le projet :
   ```bash
   git clone <url-du-dépôt>
   cd <nom-du-dossier>
   ```

2. Installez les dépendances :
   ```bash
   composer install
   ```

3. Configurez votre base de données dans le fichier `.env` :
   ```dotenv
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/nom_de_la_base"
   ```

4. Créez le schéma de la base de données :
   ```bash
   symfony console doctrine:database:create
   symfony console doctrine:migrations:migrate
   ```

5. Insérez des données de test :
   ```bash
   symfony console doctrine:fixtures:load
   ```

6. Lancez le serveur de développement :
   ```bash
   symfony serve
   ```

7. Accédez à l'application :
    - URL principale : `http://localhost:8000`
    - API Swagger : `http://localhost:8000/api/doc`

---

## Utiliser le service

### Cas nominal d'utilisation
- **Lister les jours disponibles** :
  ```bash
  curl -X GET http://localhost:8000/jours
  ```
- **Créer un utilisateur** :
  ```bash
  curl -X POST http://localhost:8000/users/{pseudo}
  ```
  Remplacez `{pseudo}` par le pseudo souhaité.

- **Lister les créneaux horaires disponibles** :
  ```bash
  curl -X GET http://localhost:8000/creneaux/horaire
  ```

- **Afficher les créneaux pour un jour spécifique** :
  ```bash
  curl -X GET http://localhost:8000/creneaux/{jour}
  ```
  Remplacez `{jour}` par le jour souhaité (ex : lundi, mardi, etc.).

- **Récupérer une réservation par ID** :
  ```bash
  curl -X GET http://localhost:8000/reservations/{id}
  ```
  Remplacez `{id}` par l'ID de la réservation.

- **Créer une nouvelle réservation** :
  ```bash
  curl -X POST http://localhost:8000/reservations -d '{"pseudo": "{pseudo}", "terrain": "{terrain}", "jour": "{jour}", "creneau": "{creneau}"}' -H "Content-Type: application/json"
  ```
  Remplacez `{pseudo}`, `{terrain}`, `{jour}`, `{creneau}` par les valeurs souhaitées.

- **Lister toutes les réservations** :
  ```bash
  curl -X GET http://localhost:8000/reservations
  ```

- **Lister les terrains disponibles** :
  ```bash
  curl -X GET http://localhost:8000/terrains
  ```

- **Lister les terrains actifs** :
  ```bash
  curl -X GET http://localhost:8000/terrains/disponible
  ```
  
- **Rendre un terrain disponible (actif)** :
  ```bash
  curl -X PATCH http://localhost:8000/terrain/disponible/{id}
  ```
  Remplacez `{id}` par l'ID du terrain.

- **Rendre un terrain indisponible (inactif)** :
  ```bash
  curl -X PATCH http://localhost:8000/terrain/indisponible/{id}
  ```
  Remplacez `{id}` par l'ID du terrain.

- **Récupérer les informations de l'utilisateur authentifié** :
  ```bash
  curl -X GET http://localhost:8000/user
  ```
  Problème de token : ne fonctionne pas

- **Récupérer le rôle de l'utilisateur authentifié** :
  ```bash
  curl -X GET http://localhost:8000/api/user/roles
  ```
  Problème de token : ne fonctionne pas

- **Créer un utilisateur** :
  ```bash
  curl -X POST http://localhost:8000/api/users/{pseudo}
  ```
  Remplacez `{pseudo}` par le pseudo souhaité.

---

## Conception

### Dictionnaire des données
| Entité         | Attribut      | Type     | Description                                                            | Exemple                                                      |
|----------------|---------------|----------|------------------------------------------------------------------------|--------------------------------------------------------------|
| **Utilisateur**| pseudo        | string   | Nom d'utilisateur.                                                     | "Louen", "Paul", "amybad"                                    |
|                | isAdmin       | bool     | Statut de l'utilisateur (`true` pour admin, `false` pour utilisateur)  | `false` pour Louen et Paul, `true` pour amybad               |
|                | password      | string   | Mot de passe hashé de l'utilisateur.                                    | "root" pour Louen et Paul, "astrongpassword" pour amybad     |
| **Terrain**    | nom           | string   | Nom du terrain.                                                         | "Terrain_A", "Terrain_B", "Terrain_C", "Terrain_D"           |
|                | actif         | bool     | Statut du terrain, `true` signifie actif.                               | `true`                                                        |
| **Jour**       | jour          | string   | Nom du jour de la semaine.                                             | "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"  |
| **CreneauHoraire** | creneau   | string   | Plage horaire pour la réservation.   

---

### Tableau récapitulatif des ressources
| Route                                      | Méthode HTTP | Description                                                              | Paramètres requis                                     | Réponse                                      |
|--------------------------------------------|--------------|--------------------------------------------------------------------------|-------------------------------------------------------|----------------------------------------------|
| **/jours**                                 | GET          | Liste tous les jours disponibles.                                         | Aucun                                                 | JSON : Liste des jours avec `id` et `jour`    |
| **/reservations/{id}**                     | GET          | Récupère une réservation par ID.                                          | `id` (ID de la réservation)                           | JSON : Détails de la réservation avec `id`, `utilisateur`, `terrain`, `jour`, `creneau` |
| **/reservations**                          | POST         | Crée une nouvelle réservation.                                            | `pseudo`, `terrain`, `jour`, `creneau` (données JSON) | JSON : Message de confirmation ou d'erreur     |
| **/reservations**                          | GET          | Liste toutes les réservations, avec possibilité de filtrer par `pseudo` ou `terrain`. | `pseudo`, `terrain` (facultatif, query params)         | JSON : Liste des réservations avec `id`, `utilisateur`, `terrain`, `jour`, `creneau` |
| **/terrains**                              | GET          | Liste tous les terrains.                                                  | Aucun                                                 | JSON : Liste des terrains avec `id`, `nom`, `actif` |
| **/terrains/disponible**                   | GET          | Liste les terrains actifs (disponibles).                                  | Aucun                                                 | JSON : Liste des terrains actifs avec `id`, `nom`, `actif` |
| **/terrain/disponible/{id}**               | PATCH        | Rend un terrain disponible (actif).                                       | `id` (ID du terrain)                                  | JSON : Message de confirmation ou d'erreur     |
| **/terrain/indisponible/{id}**             | PATCH        | Rend un terrain indisponible (inactif).                                   | `id` (ID du terrain)                                  | JSON : Message de confirmation ou d'erreur     |
| **/user**                                  | GET          | Récupère les informations de l'utilisateur authentifié.                   | Aucun                                                 | JSON : Détails de l'utilisateur avec `username`, `roles` |
| **/user/roles**                            | GET          | Récupère les rôles de l'utilisateur authentifié.                          | Aucun                                                 | JSON : Liste des rôles de l'utilisateur       |
| **/users/{pseudo}**                        | POST         | Crée un nouvel utilisateur avec un pseudo spécifié.                       | `pseudo` (pseudo de l'utilisateur)                    | JSON : Message de confirmation ou d'erreur     |
---

## Sécurité
- **Authentification JWT** :
    - Utilisation du bundle LexikJWTAuthenticationBundle.
    - Les routes sensibles nécessitent un token valide.
    - Normalement pour gérer l'accès à une ressource réserver au admin il faut utiliser cette ligne de code:
    ```bash
      #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour cette action')]
    ```
    Mais du au p^roblème de token je ne pouvait pas récupérer le rôle.

- **Hashage des mots de passe** :
    - Les mots de passe sont hashés avec `UserPasswordHasherInterface`.

- **Rôles et autorisations** :
    - Gestion des rôles utilisateur pour restreindre les accès.

---

## Remarques
- J'ai créer une seule table de donnée utilisateur et utilisé un système de jeton (JWT), ce qui a posé pas mal de problème puisque
j'étais obligé ensuite d'utilisé des mot de passes pour tout les utilisateur d'ou l'initialisation du mot de passe root pour les 
nouveaux utilisateurs. Cela aurait pu me permettre de mieux gérer l'accès à certaine route ce que je n'ai pas pu faire ici. Après
réflexion j'aurai du créer une table Admin et une table Utilisateur qui m'aurai permis de ne pas mélanger les deux et pour mettre
un jeton uniquement sur l'Admin.
- Symfony est un très bon outil pour créer une Api notamment grâce à son ORM Doctrine qui propose beaucoup de bibliothèques très utils. 
Mais c'est aussi un outils complexe qui m'a dait perdre pas mal de temps sur un projet aussi court.
- Respect du format HAL notamment grâce à la librairie fos_rest.yaml.
- Tentative pour résoudre la requête GraphlQL notamment avec la bibliothèque overblog_graphql mais infructeux.
- Une documentation Swagger est intégrée pour faciliter l'exploration des endpoints API.


---

## Références
- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [OpenClassRoom Api Symfony](https://openclassrooms.com/fr/courses/7709361-construisez-une-api-rest-avec-symfony)
- [SymfonyCast Swagger](https://symfonycasts.com/screencast/api-platform/swagger)