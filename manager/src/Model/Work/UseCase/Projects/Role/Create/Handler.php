<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Role\Create;

use App\Model\Flusher;
use App\Model\Work\Entity\Projects\Role\Role;
use App\Model\Work\Entity\Projects\Role\Id;
use App\Model\Work\Entity\Projects\Role\RoleRepository;

class Handler
{
    private $roles;
    private $flusher;

    public function __construct(RoleRepository $roles, Flusher $flusher)
    {
        $this->roles = $roles;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($this->roles->hasByName($command->name)) {
            throw new \DomainException('Role already exists.');
        }

        $role = new Role(
            Id::next(),
            $command->name,
            $command->permissions
        );

        $this->roles->add($role);

        $this->flusher->flush();
    }
}
