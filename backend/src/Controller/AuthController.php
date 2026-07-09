<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractApiController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = $this->decode($request);
        if ($data === null) {
            return $this->badRequest('Corps de requête JSON invalide.');
        }

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');

        if ($email !== '' && $this->users->findOneByEmail($email) !== null) {
            return $this->badRequest('Un compte existe déjà avec cet email.');
        }

        if (strlen($password) < 8) {
            return $this->badRequest('Le mot de passe doit contenir au moins 8 caractères.');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName((string) ($data['firstName'] ?? ''));
        $user->setLastName((string) ($data['lastName'] ?? ''));

        if ($errorResponse = $this->validate($user, $this->validator)) {
            return $errorResponse;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->em->persist($user);
        $this->createDefaultCategories($user);
        $this->em->flush();

        // Connecte directement l'utilisateur après son inscription (évite un aller-retour de login).
        return $this->json([
            'token' => $this->jwtManager->create($user),
            'user' => $user->toArray(),
        ], 201);
    }

    /**
     * Crée un jeu de catégories de départ pour qu'un nouvel utilisateur puisse tout de
     * suite ajouter des transactions, sans avoir à créer ses catégories une par une.
     */
    private function createDefaultCategories(User $user): void
    {
        $defaults = [
            'Salaire' => '#22c55e',
            'Alimentation' => '#f97316',
            'Logement' => '#0ea5e9',
            'Transport' => '#6366f1',
            'Loisirs' => '#ec4899',
            'Santé' => '#14b8a6',
            'Abonnements' => '#a855f7',
        ];

        foreach ($defaults as $name => $color) {
            $category = new Category();
            $category->setName($name);
            $category->setColor($color);
            $category->setOwner($user);
            $this->em->persist($category);
        }
    }

    /**
     * Cette route n'est jamais réellement exécutée : elle existe uniquement pour que le
     * routeur Symfony reconnaisse /api/login (sinon RouterListener renvoie 404 avant même
     * que le firewall "login" ait la main). L'authentification est interceptée plus tôt par
     * le JsonLoginAuthenticator configuré dans security.yaml (json_login / check_path).
     */
    #[Route('/api/login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Cette route est interceptée par le firewall JWT avant d\'arriver ici.');
    }

    #[Route('/api/me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            return $this->json(['error' => 'Non authentifié.'], 401);
        }

        return $this->json($user->toArray());
    }
}
