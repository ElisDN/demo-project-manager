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
    private $name;
    private $email;

    public function __construct()
    {
        $this->name = new Name('First', 'Last');
        $this->email = new Email('member@app.test');
    }

    public function build(Group $group): Member
    {
        return new Member(
            Id::next(),
            $group,
            $this->name,
            $this->email
        );
    }
}
