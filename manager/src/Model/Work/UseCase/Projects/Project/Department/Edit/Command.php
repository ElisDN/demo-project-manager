<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Project\Department\Edit;

use App\Model\Work\Entity\Projects\Project\Department\Department;
use App\Model\Work\Entity\Projects\Project\Project;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $project;
    /**
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(string $project, string $id)
    {
        $this->project = $project;
        $this->id = $id;
    }

    public static function fromDepartment(Project $project, Department $department): self
    {
        $command = new self($project->getId()->getValue(), $department->getId()->getValue());
        $command->name = $department->getName();
        return $command;
    }
}
