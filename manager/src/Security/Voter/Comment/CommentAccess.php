<?php

declare(strict_types=1);

namespace App\Security\Voter\Comment;

use App\Model\Comment\Entity\Comment\Comment;
use App\ReadModel\Comment\CommentRow;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentAccess extends Voter
{
    public const MANAGE = 'manage';

    private $security;

    public function __construct(AuthorizationCheckerInterface $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject): bool
    {
        return $attribute === self::MANAGE && ($subject instanceof Comment || $subject instanceof CommentRow);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $own = false;

        if ($subject instanceof Comment) {
            $own = $subject->getAuthorId()->getValue() === $user->getId();
        }

        if ($subject instanceof CommentRow) {
            $own = $subject->author_id === $user->getId();
        }

        switch ($attribute) {
            case self::MANAGE:
                return
                    $this->security->isGranted('ROLE_WORK_MANAGE_PROJECTS') || $own;
                break;
        }

        return false;
    }
}
