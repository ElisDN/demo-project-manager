<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Members\Member\Edit;

use App\Model\Flusher;
use App\Model\Work\Entity\Members\Member\Email;
use App\Model\Work\Entity\Members\Member\Id;
use App\Model\Work\Entity\Members\Member\MemberRepository;
use App\Model\Work\Entity\Members\Member\Name;

class Handler
{
    private $members;
    private $flusher;

    public function __construct(MemberRepository $members, Flusher $flusher)
    {
        $this->members = $members;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $member = $this->members->get(new Id($command->id));

        $member->edit(
            new Name(
                $command->firstName,
                $command->lastName
            ),
            new Email($command->email)
        );

        $this->flusher->flush();
    }
}