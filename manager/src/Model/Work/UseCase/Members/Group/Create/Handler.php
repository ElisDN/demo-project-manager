<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Members\Group\Create;

use App\Model\Flusher;
use App\Model\Work\Entity\Members\Group\Group;
use App\Model\Work\Entity\Members\Group\Id;
use App\Model\Work\Entity\Members\Group\GroupRepository;

class Handler
{
    private $groups;
    private $flusher;

    public function __construct(GroupRepository $groups, Flusher $flusher)
    {
        $this->groups = $groups;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $group = new Group(
            Id::next(),
            $command->name
        );

        $this->groups->add($group);

        $this->flusher->flush();
    }
}
