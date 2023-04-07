<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserConfirmation implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // if ($user->isDeleted()) {
        //     // the message passed to this exception is meant to be displayed to the user
        //     throw new CustomUserMessageAccountStatusException('Your user account no longer exists.');
        // }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isIsVerified()) {
            throw new CustomUserMessageAccountStatusException('Votre compte n\' est pas vérifié, merci de le confirmer avant le ' . $user->getTokenRegistrationLifeTime()->format('d/m/Y à H:i:s'));
        }

        // // user account is expired, the user may be notified
        // if ($user->isExpired()) {
        //     throw new AccountExpiredException('...');
        // }
    }
}
