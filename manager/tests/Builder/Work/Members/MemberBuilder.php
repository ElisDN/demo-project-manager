<?php

declare(strict_types=1);

namespace App\Tests\Builder\Work\Members;

use App\Model\Work\Entity\Members\Group\Group;
use App\Model\Work\Entity\Members\Member\Email;
use App\Model\Work\Entity\Members\Member\Id;
use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Members\Member\Name;

class MemberBuilder
{
    private $id;
    private $name;
    private $email;

    public function __construct()
    {
        $this->id = Id::next();
        $this->name = new Name('First', 'Last');
        $this->email = new Email('member@app.test');
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withEmail(Email $email): self
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function build(Group $group): Member
    {
        return new Member(
            $this->id,
            $group,
            $this->name,
            $this->email
        );
    }
}
