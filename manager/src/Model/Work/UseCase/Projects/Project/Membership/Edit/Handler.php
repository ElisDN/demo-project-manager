<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Project\Membership\Edit;

use App\Model\Flusher;
use App\Model\Work\Entity\Projects\Project\Department\Id as DepartmentId;
use App\Model\Work\Entity\Projects\Project\Id;
use App\Model\Work\Entity\Projects\Project\ProjectRepository;
use App\Model\Work\Entity\Members\Member\MemberRepository;
use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Projects\Role\Id as RoleId;
use App\Model\Work\Entity\Projects\Role\Role;
use App\Model\Work\Entity\Projects\Role\RoleRepository;

class Handler
{
    private $projects;
    private $members;
    private $roles;
    private $flusher;

    public function __construct(
        ProjectRepository $projects,
        MemberRepository $members,
        RoleRepository $roles,
        Flusher $flusher
    )
    {
        $this->projects = $projects;
        $this->members = $members;
        $this->roles = $roles;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $project = $this->projects->get(new Id($command->project));
        $member = $this->members->get(new MemberId($command->member));

        $departments = array_map(static function (string $id): DepartmentId {
            return new DepartmentId($id);
        }, $command->departments);

        $roles = array_map(function (string $id): Role {
            return $this->roles->get(new RoleId($id));
        }, $command->roles);

        $project->editMember($member->getId(), $departments, $roles);

        $this->flusher->flush();
    }
}

