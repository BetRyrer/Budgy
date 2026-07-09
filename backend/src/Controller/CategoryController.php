<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategoryController extends AbstractApiController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CategoryRepository $categories,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        $categories = $this->categories->findAllForOwner($user);

        return $this->json(array_map(fn (Category $c) => $c->toArray(), $categories));
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $category = $this->categories->findOneForOwner($id, $user);
        if (!$category) {
            return $this->notFound('Catégorie introuvable.');
        }

        return $this->json($category->toArray());
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = $this->decode($request);
        if ($data === null) {
            return $this->badRequest('Corps de requête JSON invalide.');
        }

        $category = new Category();
        $category->setName((string) ($data['name'] ?? ''));
        $category->setColor((string) ($data['color'] ?? '#6366f1'));
        $category->setOwner($user);

        if ($errorResponse = $this->validate($category, $this->validator)) {
            return $errorResponse;
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->json($category->toArray(), 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $category = $this->categories->findOneForOwner($id, $user);
        if (!$category) {
            return $this->notFound('Catégorie introuvable.');
        }

        $data = $this->decode($request);
        if ($data === null) {
            return $this->badRequest('Corps de requête JSON invalide.');
        }

        if (array_key_exists('name', $data)) {
            $category->setName((string) $data['name']);
        }
        if (array_key_exists('color', $data)) {
            $category->setColor((string) $data['color']);
        }

        if ($errorResponse = $this->validate($category, $this->validator)) {
            return $errorResponse;
        }

        $this->em->flush();

        return $this->json($category->toArray());
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $category = $this->categories->findOneForOwner($id, $user);
        if (!$category) {
            return $this->notFound('Catégorie introuvable.');
        }

        if (!$category->getTransactions()->isEmpty()) {
            return $this->badRequest('Impossible de supprimer une catégorie utilisée par des transactions.');
        }

        $this->em->remove($category);
        $this->em->flush();

        return $this->json(null, 204);
    }
}
