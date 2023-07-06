<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener
{

    /**
     * Summary of hasher
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    //Encode password before persisted
    public function prePersist(User $user): void
    {
        $this->encodePassword($user);
    }

    // Encode new password before updated
    public function preUpdate(User $user): void
    {
        $this->encodePassword($user);
    }

    /**
     * Encode password based on plainPassword
     * @param User $user
     * @return void
     */
    public function encodePassword(User $user): void
    {
        if ($user->getPlainPassword() === null) return;

        $user->setPassword(
            $this->hasher->hashPassword(
                $user,
                $user->getPlainPassword()
            )
        );

        $user->setPlainPassword(null);
    }
}
