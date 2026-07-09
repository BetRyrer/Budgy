<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Ajoute les informations de l'utilisateur (prénom, nom, email) à la réponse de connexion,
 * pour éviter au frontend un second appel à /api/me juste après le login.
 */
#[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success')]
class JwtAuthenticationSuccessListener
{
    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $data = $event->getData();
        $data['user'] = $user->toArray();
        $event->setData($data);
    }
}
