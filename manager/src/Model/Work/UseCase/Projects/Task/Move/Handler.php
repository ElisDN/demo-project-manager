<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Move;

use App\Model\Flusher;
use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Members\Member\MemberRepository;
use App\Model\Work\Entity\Projects\Task\Id;
use App\Model\Work\Entity\Projects\Task\TaskRepository;
use App\Model\Work\Entity\Projects\Project\ProjectRepository;
use App\Model\Work\Entity\Projects\Project\Id as ProjectId;

class Handler
{
    private $members;
    private $tasks;
    private $flusher;
    private $projects;

    public function __construct(
        MemberRepository $members,
        TaskRepository $tasks,
        ProjectRepository $projects,
        Flusher $flusher
    )
    {
        $this->members = $members;
        $this->tasks = $tasks;
        $this->flusher = $flusher;
        $this->projects = $projects;
    }

    public function handle(Command $command): void
    {
        $actor = $this->members->get(new MemberId($command->actor));
        $task = $this->tasks->get(new Id($command->id));
        $project = $this->projects->get(new ProjectId($command->project));

        $task->move($actor, new \DateTimeImmutable(), $project);

        if ($command->withChildren) {
            $children = $this->tasks->allByParent($task->getId());
            foreach ($children as $child) {
                $child->move($actor, new \DateTimeImmutable(), $project);
            }
        }

        $this->flusher->flush($task);
    }
}

