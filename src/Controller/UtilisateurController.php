<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;

class UtilisateurController extends AbstractController
{
    private $jwtManager;
    private $security;

    public function __construct(JWTTokenManagerInterface $jwtManager, Security $security)
    {
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    #[Route('/user', name: 'user_infos', methods: ['GET'])]
    public function getUserInfo(LoggerInterface $logger)
    {
        $user = $this->security->getUser();

        if (!$user) {
            $logger->error('Utilisateur non authentifié.');
            throw $this->createAccessDeniedException('Access denied');
        }

        $logger->info('Utilisateur authentifié : ', [
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);

        try {
            $tokenData = $this->jwtManager->decode($user);

            return $this->json([
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'token_data' => $tokenData,
            ]);
        } catch (\Exception $e) {
            $logger->error('Erreur lors du décodage du token JWT.', [
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Erreur JWT.');
        }
    }

    #[Route('/user/roles', name: 'user_roles', methods: ['GET'])]
    public function getUserRoles(Security $security): JsonResponse
    {
        // Récupère l'utilisateur authentifié
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        // Récupère les rôles de l'utilisateur
        $roles = $user->getRoles();

        // Retourner les rôles dans la réponse
        return new JsonResponse(['roles' => $roles], Response::HTTP_OK);
    }

    // Route API pour créer un utilisateur avec un pseudo passé dans l'URL
    #[Route('/users/{pseudo}', name: 'api_user_create', methods: ['POST'])]
    public function createUser(string $pseudo, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        if (empty($pseudo)) {
            return new JsonResponse(['message' => 'Le pseudo est obligatoire.'], Response::HTTP_BAD_REQUEST);
        }

        $user = new Utilisateur();
        $user->setPseudo($pseudo);
        $user->setIsAdmin(false);
        $user->setPassword($passwordHasher->hashPassword($user, 'root'));

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur créé avec succès. Mot de passe : "root"'], Response::HTTP_CREATED);
    }
}
