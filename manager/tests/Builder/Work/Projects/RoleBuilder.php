<?php

declare(strict_types=1);

namespace App\Tests\Builder\Work\Projects;

use App\Model\Work\Entity\Projects\Role\Id;
use App\Model\Work\Entity\Projects\Role\Role;

class RoleBuilder
{
    private $id;
    private $name;
    private $permissions;

    public function __construct()
    {
        $this->id = Id::next();
        $this->name = 'Role';
        $this->permissions = [];
    }

    public function withName(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withPermissions(array $permissions): self
    {
        $clone = clone $this;
        $clone->permissions = $permissions;
        return $clone;
    }

    public function build(): Role
    {
        return new Role(
            $this->id,
            $this->name,
            $this->permissions
        );
    }
}
