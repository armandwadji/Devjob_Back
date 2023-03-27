<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener
{

    /**
     * Summary of hasher
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    //Encode le password avant de le persister
    public function prePersist(User $user)
    {
        $this->encodePassword($user);
    }

    // Encode le nouveau password lorque on va le modifier
    public function preUpdate(User $user)
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
