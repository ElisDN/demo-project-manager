<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Project;

use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Project\Department\Department;
use App\Model\Work\Entity\Projects\Project\Department\Id as DepartmentId;
use App\Model\Work\Entity\Projects\Role\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="work_projects_project_memberships", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"project_id", "member_id"})
 * })
 */
class Membership
{
    /**
     * @var string
     * @ORM\Column(type="guid")
     * @ORM\Id
     */
    private $id;
    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="memberships")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     */
    private $project;
    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="App\Model\Work\Entity\Members\Member\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", nullable=false)
     */
    private $member;
    /**
     * @var ArrayCollection|Department[]
     * @ORM\ManyToMany(targetEntity="App\Model\Work\Entity\Projects\Project\Department\Department")
     * @ORM\JoinTable(name="work_projects_project_membership_departments",
     *     joinColumns={@ORM\JoinColumn(name="membership_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="department_id", referencedColumnName="id")}
     * )
     */
    private $departments;
    /**
     * @var ArrayCollection|Role[]
     * @ORM\ManyToMany(targetEntity="App\Model\Work\Entity\Projects\Role\Role")
     * @ORM\JoinTable(name="work_projects_project_membership_roles",
     *     joinColumns={@ORM\JoinColumn(name="membership_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    private $roles;

    /**
     * Membership constructor.
     * @param Project $project
     * @param Member $member
     * @param ArrayCollection|Department[] $departments
     * @param ArrayCollection|Role[] $roles
     * @throws \Exception
     */
    public function __construct(Project $project, Member $member, array $departments, array $roles)
    {
        $this->guardDepartments($departments);
        $this->guardRoles($roles);

        $this->id = Uuid::uuid4()->toString();
        $this->project = $project;
        $this->member = $member;
        $this->departments = new ArrayCollection($departments);
        $this->roles = new ArrayCollection($roles);
    }

    /**
     * @param Department[] $departments
     */
    public function changeDepartments(array $departments): void
    {
        $this->guardDepartments($departments);

        $current = $this->departments->toArray();
        $new = $departments;

        $compare = static function (Department $a, Department $b): int {
            return $a->getId()->getValue() <=> $b->getId()->getValue();
        };

        foreach (array_udiff($current, $new, $compare) as $item) {
            $this->departments->removeElement($item);
        }

        foreach (array_udiff($new, $current, $compare) as $item) {
            $this->departments->add($item);
        }
    }

    /**
     * @param Role[] $roles
     */
    public function changeRoles(array $roles): void
    {
        $this->guardRoles($roles);

        $current = $this->roles->toArray();
        $new = $roles;

        $compare = static function (Role $a, Role $b): int {
            return $a->getId()->getValue() <=> $b->getId()->getValue();
        };

        foreach (array_udiff($current, $new, $compare) as $item) {
            $this->roles->removeElement($item);
        }

        foreach (array_udiff($new, $current, $compare) as $item) {
            $this->roles->add($item);
        }
    }

    public function isForMember(MemberId $id): bool
    {
        return $this->member->getId()->isEqual($id);
    }

    public function isForDepartment(DepartmentId $id): bool
    {
        foreach ($this->departments as $department) {
            if ($department->getId()->isEqual($id)) {
                return true;
            }
        }
        return false;
    }

    public function isGranted(string $permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    /**
     * @return Role[]
     */
    public function getRoles(): array
    {
        return $this->roles->toArray();
    }

    /**
     * @return Department[]
     */
    public function getDepartments(): array
    {
        return $this->departments->toArray();
    }

    public function guardDepartments(array $departments): void
    {
        if (\count($departments) === 0) {
            throw new \DomainException('Set at least one department.');
        }
    }

    public function guardRoles(array $roles): void
    {
        if (\count($roles) === 0) {
            throw new \DomainException('Set at least one role.');
        }
    }
}
