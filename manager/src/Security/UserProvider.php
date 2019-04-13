<?php

declare(strict_types=1);

namespace App\Security;

use App\ReadModel\User\UserFetcher;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private $users;

    public function __construct(UserFetcher $users)
    {
        $this->users = $users;
    }

    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->users->findForAuth($username);

        if (!$user) {
            throw new UsernameNotFoundException('');
        }

        return new UserIdentity(
            $user->id,
            $user->email,
            $user->password_hash,
            $user->role
        );
    }

    public function refreshUser(UserInterface $identity): UserInterface
    {
        if (!$identity instanceof UserIdentity) {
            throw new UnsupportedUserException('Invalid user class ' . \get_class($identity));
        }

        return $identity;
    }

    public function supportsClass($class): bool
    {
        return $class instanceof UserIdentity;
    }
}
