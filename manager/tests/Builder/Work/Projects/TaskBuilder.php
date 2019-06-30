<?php

declare(strict_types=1);

namespace App\Tests\Builder\Work\Projects;

use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Project\Project;
use App\Model\Work\Entity\Projects\Task\Id;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Model\Work\Entity\Projects\Task\Type;

class TaskBuilder
{
    private $id;
    private $date;
    private $type;
    private $priority;
    private $name;
    private $content;

    public function __construct()
    {
        $this->id = new Id(1);
        $this->date = new \DateTimeImmutable();
        $this->type = new Type(Type::FEATURE);
        $this->priority = 1;
        $this->name = 'Task';
        $this->content = 'Content';
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withType(Type $type): self
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function build(Project $project, Member $author): Task
    {
        return new Task(
            $this->id,
            $project,
            $author,
            $this->date,
            $this->type,
            $this->priority,
            $this->name,
            $this->content
        );
    }
}
