<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The user.registered event is dispatched each time an user is registered
 * in the system.
 */
class UserRegisteredEvent extends Event
{
    public const NAME = 'user.registered';

    public function __construct(
        protected User $user,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
