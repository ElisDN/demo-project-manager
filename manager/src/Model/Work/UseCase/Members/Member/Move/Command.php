<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Members\Member\Move;

use App\Model\Work\Entity\Members\Member\Member;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @Assert\NotBlank()
     */
    public $group;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromMember(Member $member): self
    {
        $command = new self($member->getId()->getValue());
        $command->group = $member->getGroup()->getId()->getValue();
        return $command;
    }
}
