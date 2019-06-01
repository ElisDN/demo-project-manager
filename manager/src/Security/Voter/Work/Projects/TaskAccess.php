<?php

declare(strict_types=1);

namespace App\Security\Voter\Work\Projects;

use App\Model\Work\Entity\Members\Member\Id;
use App\Model\Work\Entity\Projects\Role\Permission;
use App\Model\Work\Entity\Projects\Task\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskAccess extends Voter
{
    public const VIEW = 'view';
    public const MANAGE = 'edit';
    public const DELETE = 'delete';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::MANAGE, self::DELETE], true) && $subject instanceof Task;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof Task) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return
                    $this->security->isGranted('ROLE_WORK_MANAGE_PROJECTS') ||
                    $subject->getProject()->isMemberGranted(new Id($user->getId()), Permission::VIEW_TASKS);
                break;
            case self::MANAGE:
                return
                    $this->security->isGranted('ROLE_WORK_MANAGE_PROJECTS') ||
                    $subject->getProject()->isMemberGranted(new Id($user->getId()), Permission::MANAGE_TASKS);
                break;
            case self::DELETE:
                return
                    $this->security->isGranted('ROLE_WORK_MANAGE_PROJECTS');
                break;
        }

        return false;
    }
}
