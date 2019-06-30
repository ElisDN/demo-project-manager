<?php

declare(strict_types=1);

namespace App\Tests\Builder\Work\Members;

use App\Model\Work\Entity\Members\Group\Group;
use App\Model\Work\Entity\Members\Group\Id;

class GroupBuilder
{
    private $name;

    public function __construct()
    {
        $this->name = 'Group';
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function build(): Group
    {
        return new Group(
            Id::next(),
            $this->name
        );
    }
}
