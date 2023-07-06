<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserConfirmation implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isIsDeleted()) {
            throw new CustomUserMessageAccountStatusException('Votre compte à été supprimé.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        
        // user account is expired, the user may be notified
        if ($user->isExpired()) {
            throw new CustomUserMessageAccountStatusException('Votre token à expirer. Veuillez refaire une demande d\'inscription.');
        }

        if (!$user->isIsVerified()) {
            throw new CustomUserMessageAccountStatusException('Votre compte n\' est pas vérifié, merci de le confirmer avant le ' . $user->getTokenRegistrationLifeTime()->format('d/m/Y à H:i:s'));
        }

    }
}
