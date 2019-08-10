<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="work_projects_roles")
 */
class Role
{
    /**
     * @var Id
     * @ORM\Column(type="work_projects_role_id")
     * @ORM\Id
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    private $name;
    /**
     * @var ArrayCollection|Permission[]
     * @ORM\Column(type="work_projects_role_permissions")
     */
    private $permissions;
    /**
     * @ORM\Version()
     * @ORM\Column(type="integer")
     */
    private $version;

    /**
     * Role constructor.
     * @param Id $id
     * @param string $name
     * @param string[] $permissions
     */
    public function __construct(Id $id, string $name, array $permissions)
    {
        $this->id = $id;
        $this->name = $name;
        $this->setPermissions($permissions);
    }

    /**
     * @param string $name
     * @param string[] $permissions
     */
    public function edit(string $name, array $permissions): void
    {
        $this->name = $name;
        $this->setPermissions($permissions);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions->exists(static function (int $key, Permission $current) use ($permission) {
            return $current->isNameEqual($permission);
        });
    }

    public function clone(Id $id, string $name): self
    {
        return new self($id, $name, array_map(static function (Permission $permission) {
            return $permission->getName();
        }, $this->permissions->toArray()));
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions->toArray();
    }

    public function setPermissions(array $names): void
    {
        $this->permissions = new ArrayCollection(array_map(static function (string $name) {
            return new Permission($name);
        }, array_unique($names)));
    }
}
