<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Project\Department\Edit;

use App\Model\Flusher;
use App\Model\Work\Entity\Projects\Project\Department\Id as DepartmentId;
use App\Model\Work\Entity\Projects\Project\Id;
use App\Model\Work\Entity\Projects\Project\ProjectRepository;

class Handler
{
    private $projects;
    private $flusher;

    public function __construct(ProjectRepository $projects, Flusher $flusher)
    {
        $this->projects = $projects;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $project = $this->projects->get(new Id($command->project));

        $project->editDepartment(new DepartmentId($command->id), $command->name);

        $this->flusher->flush();
    }
}

