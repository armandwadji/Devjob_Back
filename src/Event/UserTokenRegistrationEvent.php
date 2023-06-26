<?php

namespace App\Event;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserTokenRegistrationEvent extends Event
{
    public function __construct(
        public readonly User $user
    ){}
}