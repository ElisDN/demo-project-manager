<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Role\Edit;

use App\Model\Work\Entity\Projects\Role\Permission;
use App\Model\Work\Entity\Projects\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;
    /**
     * @var string[]
     */
    public $permissions;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromRole(Role $role): self
    {
        $command = new self($role->getId()->getValue());
        $command->name = $role->getName();
        $command->permissions = array_map(static function (Permission $permission): string {
            return $permission->getName();
        }, $role->getPermissions());
        return $command;
    }
}
