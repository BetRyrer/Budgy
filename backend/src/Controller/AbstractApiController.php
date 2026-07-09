<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Petites aides communes aux contrôleurs REST : décodage JSON, réponses
 * d'erreur normalisées et validation d'entité, sans dépendre du
 * AbstractController complet de Symfony (pas besoin de templating ici).
 */
abstract class AbstractApiController
{
    protected function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decode(Request $request): ?array
    {
        $content = $request->getContent();
        if ($content === '') {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : null;
    }

    protected function badRequest(string $message): JsonResponse
    {
        return $this->json(['error' => $message], 400);
    }

    protected function notFound(string $message): JsonResponse
    {
        return $this->json(['error' => $message], 404);
    }

    /**
     * Valide l'entité et renvoie une JsonResponse 422 en cas d'erreurs, ou null si tout est valide.
     */
    protected function validate(object $entity, ValidatorInterface $validator): ?JsonResponse
    {
        $violations = $validator->validate($entity);
        if (count($violations) === 0) {
            return null;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $this->json(['error' => 'Validation échouée.', 'details' => $errors], 422);
    }
}
