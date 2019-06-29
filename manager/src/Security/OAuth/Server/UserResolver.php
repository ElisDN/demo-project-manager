<?php

declare(strict_types=1);

namespace App\Security\OAuth\Server;

use App\Model\User\Service\PasswordHasher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\UserResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

final class UserResolver implements EventSubscriberInterface
{
    private $userProvider;
    private $hasher;

    public function __construct(UserProviderInterface $userProvider, PasswordHasher $hasher)
    {
        $this->userProvider = $userProvider;
        $this->hasher = $hasher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::USER_RESOLVE => 'onUserResolve',
        ];
    }

    public function onUserResolve(UserResolveEvent $event): void
    {
        $user = $this->userProvider->loadUserByUsername($event->getUsername());

        if (null === $user) {
            return;
        }

        if (!$user->getPassword()) {
            return;
        }

        if (!$this->hasher->validate($event->getPassword(), $user->getPassword())) {
            return;
        }

        $event->setUser($user);
    }
}
