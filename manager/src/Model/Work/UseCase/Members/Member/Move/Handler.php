<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Members\Member\Move;

use App\Model\Flusher;
use App\Model\Work\Entity\Members\Group\GroupRepository;
use App\Model\Work\Entity\Members\Group\Id as GroupId;
use App\Model\Work\Entity\Members\Member\Id;
use App\Model\Work\Entity\Members\Member\MemberRepository;

class Handler
{
    private $members;
    private $groups;
    private $flusher;

    public function __construct(MemberRepository $members, GroupRepository $groups, Flusher $flusher)
    {
        $this->members = $members;
        $this->groups = $groups;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $member = $this->members->get(new Id($command->id));
        $group = $this->groups->get(new GroupId($command->group));

        $member->move($group);

        $this->flusher->flush();
    }
}