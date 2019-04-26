<?php

declare(strict_types=1);

namespace App\Security;

use App\ReadModel\User\AuthView;
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
        $user = $this->loadUser($username);
        return self::identityByUser($user, $username);
    }

    public function refreshUser(UserInterface $identity): UserInterface
    {
        if (!$identity instanceof UserIdentity) {
            throw new UnsupportedUserException('Invalid user class ' . \get_class($identity));
        }

        $user = $this->loadUser($identity->getUsername());
        return self::identityByUser($user, $identity->getUsername());
    }

    public function supportsClass($class): bool
    {
        return $class === UserIdentity::class;
    }

    private function loadUser($username): AuthView
    {
        $chunks = explode(':', $username);

        if (\count($chunks) === 2 && $user = $this->users->findForAuthByNetwork($chunks[0], $chunks[1])) {
            return $user;
        }

        if ($user = $this->users->findForAuthByEmail($username)) {
            return $user;
        }

        throw new UsernameNotFoundException('');
    }

    private static function identityByUser(AuthView $user, string $username): UserIdentity
    {
        return new UserIdentity(
            $user->id,
            $user->email ?: $username,
            $user->password_hash ?: '',
            $user->role,
            $user->status
        );
    }
}
